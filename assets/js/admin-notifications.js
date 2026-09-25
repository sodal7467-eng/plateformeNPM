/**
 * Widget de notifications admin - Nouvelles commandes
 *
 * - Interroge /admin/api/notifications.php toutes les 30 s
 * - Met a jour le badge et le dropdown
 * - Une commande disparait automatiquement des qu'elle n'est plus "En attente"
 * - Toast + son + secousse de la cloche a chaque nouvelle commande
 */

(function () {
    "use strict";

    var POLL_INTERVAL_MS = 30000; // 30 secondes
    var STORAGE_LAST_IDS = "npm_admin_notif_last_ids";

    var btn = document.getElementById("adminNotifBtn");
    var badge = document.getElementById("adminNotifBadge");
    var panel = document.getElementById("adminNotifPanel");
    var body = document.getElementById("adminNotifBody");
    var headerCount = document.getElementById("adminNotifHeaderCount");

    if (!btn || !badge || !panel || !body) return;

    var currentIds = new Set();
    var isFirstLoad = true;

    // -----------------------------------------------------------------
    // Chargement precedent (persistance entre pages)
    // -----------------------------------------------------------------
    function loadLastIds() {
        try {
            var raw = localStorage.getItem(STORAGE_LAST_IDS);
            if (raw) return new Set(JSON.parse(raw));
        } catch (e) {}
        return new Set();
    }
    function saveLastIds(set) {
        try {
            localStorage.setItem(STORAGE_LAST_IDS, JSON.stringify(Array.from(set)));
        } catch (e) {}
    }

    var lastKnownIds = loadLastIds();

    // -----------------------------------------------------------------
    // Format d'un montant en FCFA
    // -----------------------------------------------------------------
    function fmt(n) {
        return Number(n).toLocaleString("fr-FR").replace(/,/g, " ") + " FCFA";
    }

    // -----------------------------------------------------------------
    // Rendu du dropdown
    // -----------------------------------------------------------------
    function render(data) {
        var count = data.total || 0;

        // Badge
        if (count > 0) {
            badge.textContent = count > 99 ? "99+" : count;
            badge.style.display = "flex";
            headerCount.textContent = count + " en attente";
        } else {
            badge.style.display = "none";
            headerCount.textContent = "";
        }

        // Corps
        if (!data.items || data.items.length === 0) {
            body.innerHTML =
                '<div class="admin-notif-empty">' +
                '  <div class="admin-notif-empty-icon">📭</div>' +
                '  <div>Aucune commande en attente.</div>' +
                '</div>';
            return;
        }

        var html = "";
        for (var i = 0; i < data.items.length; i++) {
            var it = data.items[i];
            html +=
                '<a href="commande-details.php?id=' + it.id + '" class="admin-notif-item">' +
                '  <div class="admin-notif-item-top">' +
                '    <span class="admin-notif-item-client">' + escapeHtml(it.client) + '</span>' +
                '    <span class="admin-notif-item-total">' + fmt(it.total) + '</span>' +
                '  </div>' +
                '  <div class="admin-notif-item-meta">' +
                '    Commande ' + it.id + ' &middot; ' + escapeHtml(it.date) +
                '  </div>' +
                '</a>';
        }
        body.innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s == null ? "" : s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    // -----------------------------------------------------------------
    // Detection des nouvelles commandes (arrivees depuis le dernier fetch)
    // -----------------------------------------------------------------
    function detectNouveautes(items) {
        var newIds = [];
        for (var i = 0; i < items.length; i++) {
            var id = items[i].id;
            currentIds.add(id);
            if (!lastKnownIds.has(id) && !isFirstLoad) {
                newIds.push(items[i]);
            }
        }
        return newIds;
    }

    // -----------------------------------------------------------------
    // Toast en haut a droite
    // -----------------------------------------------------------------
    function afficherToast(item) {
        var t = document.createElement("div");
        t.className = "admin-notif-toast";
        t.innerHTML =
            '<i class="bi bi-bell-fill"></i>' +
            '<div>' +
            '  <div style="font-size:14px;">Nouvelle commande de ' + escapeHtml(item.client) + '</div>' +
            '  <div style="font-size:12px; opacity:0.9;">' + fmt(item.total) + '</div>' +
            '</div>';
        document.body.appendChild(t);

        // Clic sur le toast = ouvrir la commande
        t.style.cursor = "pointer";
        t.addEventListener("click", function () {
            window.location.href = "commande-details.php?id=" + item.id;
        });

        // Disparition auto apres 6 s
        setTimeout(function () {
            t.classList.add("leaving");
            setTimeout(function () { t.remove(); }, 300);
        }, 6000);
    }

    // -----------------------------------------------------------------
    // Son (subtil, base64 pour eviter un fichier externe)
    // -----------------------------------------------------------------
    function jouerBip() {
        try {
            var AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            var ctx = new AudioCtx();
            var o = ctx.createOscillator();
            var g = ctx.createGain();
            o.type = "sine";
            o.frequency.value = 880;
            g.gain.value = 0.15;
            o.connect(g);
            g.connect(ctx.destination);
            o.start();
            g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            o.stop(ctx.currentTime + 0.4);
        } catch (e) {}
    }

    // -----------------------------------------------------------------
    // Fetch principal
    // -----------------------------------------------------------------
    function fetchNotifications() {
        fetch("api/notifications.php", { credentials: "same-origin" })
            .then(function (r) {
                if (!r.ok) throw new Error("HTTP " + r.status);
                return r.json();
            })
            .then(function (data) {
                currentIds = new Set();
                var nouveautes = detectNouveautes(data.items || []);
                render(data);

                if (nouveautes.length > 0) {
                    btn.classList.remove("has-new");
                    // Reflow pour redemarrer l'animation
                    void btn.offsetWidth;
                    btn.classList.add("has-new");

                    jouerBip();
                    for (var i = 0; i < nouveautes.length; i++) {
                        afficherToast(nouveautes[i]);
                    }
                }

                // Mise a jour des IDs connus
                lastKnownIds = currentIds;
                saveLastIds(lastKnownIds);
                isFirstLoad = false;
            })
            .catch(function (e) {
                // Silencieux : pas de spam de la console
            });
    }

    // -----------------------------------------------------------------
    // Ouvrir / fermer le dropdown
    // -----------------------------------------------------------------
    btn.addEventListener("click", function (e) {
        e.stopPropagation();
        panel.classList.toggle("open");
        // Un click ouvre = on considere les commandes vues (pas de re-toast)
        if (panel.classList.contains("open")) {
            lastKnownIds = currentIds;
            saveLastIds(lastKnownIds);
        }
    });

    document.addEventListener("click", function (e) {
        if (!panel.contains(e.target) && !btn.contains(e.target)) {
            panel.classList.remove("open");
        }
    });

    // -----------------------------------------------------------------
    // Demarrage
    // -----------------------------------------------------------------
    fetchNotifications();
    setInterval(fetchNotifications, POLL_INTERVAL_MS);

})();
