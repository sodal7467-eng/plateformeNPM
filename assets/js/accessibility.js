/**
 * Widget d'accessibilite pour la Plateforme NPM
 * - Agrandissement/reduction du texte
 * - Mode contraste eleve
 * - Mode niveaux de gris
 * - Lecture vocale (Web Speech API)
 * Preferences sauvegardees dans localStorage.
 */

(function () {
    "use strict";

    var STORAGE_KEY = "npm_a11y_prefs";
    var DEFAULT = { fontStep: 0, contrast: false, grayscale: false };

    // -----------------------------------------------------------------
    // Charger / sauvegarder les prefs
    // -----------------------------------------------------------------
    function loadPrefs() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return Object.assign({}, DEFAULT);
            var p = JSON.parse(raw);
            return Object.assign({}, DEFAULT, p);
        } catch (e) {
            return Object.assign({}, DEFAULT);
        }
    }

    function savePrefs(p) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(p));
        } catch (e) { /* private mode */ }
    }

    // -----------------------------------------------------------------
    // Appliquer les prefs au document
    // -----------------------------------------------------------------
    function applyPrefs(p) {
        // Taille du texte : chaque step = +10%
        var scale = 1 + p.fontStep * 0.10;
        scale = Math.max(0.8, Math.min(1.6, scale));
        document.documentElement.style.fontSize = (scale * 100) + "%";

        document.body.classList.toggle("a11y-contrast", !!p.contrast);
        document.body.classList.toggle("a11y-grayscale", !!p.grayscale);

        // Mettre a jour les etats des boutons
        var contrastBtn = document.querySelector(".a11y-btn[data-action='contrast']");
        if (contrastBtn) contrastBtn.classList.toggle("active", !!p.contrast);

        var grayBtn = document.querySelector(".a11y-btn[data-action='grayscale']");
        if (grayBtn) grayBtn.classList.toggle("active", !!p.grayscale);
    }

    // -----------------------------------------------------------------
    // Text-to-speech (Web Speech API)
    // -----------------------------------------------------------------
    var currentUtterance = null;

    function speakPage() {
        if (!("speechSynthesis" in window)) {
            alert("Votre navigateur ne supporte pas la lecture vocale.");
            return;
        }
        stopSpeaking();

        // Recuperer le texte lisible : main, sinon body
        var target = document.querySelector("main") || document.body;
        var text = target.innerText || target.textContent || "";
        text = text.replace(/\s+/g, " ").trim();
        if (text.length === 0) return;

        // Limiter a ~2000 caracteres pour eviter les blocages
        if (text.length > 2000) text = text.slice(0, 2000) + "...";

        currentUtterance = new SpeechSynthesisUtterance(text);
        currentUtterance.lang = "fr-FR";
        currentUtterance.rate = 0.95;
        currentUtterance.pitch = 1;
        currentUtterance.onend = function () {
            updateSpeakButton(false);
            currentUtterance = null;
        };
        currentUtterance.onerror = function () {
            updateSpeakButton(false);
            currentUtterance = null;
        };

        window.speechSynthesis.speak(currentUtterance);
        updateSpeakButton(true);
    }

    function stopSpeaking() {
        if ("speechSynthesis" in window) {
            window.speechSynthesis.cancel();
        }
        currentUtterance = null;
        updateSpeakButton(false);
    }

    function updateSpeakButton(isSpeaking) {
        var btn = document.querySelector(".a11y-btn[data-action='speak']");
        if (!btn) return;
        btn.innerHTML = isSpeaking
            ? '<i class="bi bi-stop-circle"></i> Arreter'
            : '<i class="bi bi-volume-up"></i> Lire';
        btn.classList.toggle("active", isSpeaking);
    }

    // -----------------------------------------------------------------
    // Traduction en Wolof via Google Translate
    // -----------------------------------------------------------------
    function translateToWolof() {
        var host = window.location.hostname;
        var currentUrl = window.location.href;

        // Google Translate ne peut PAS traduire localhost ou une IP privée
        // (il faut que la page soit accessible publiquement).
        var estLocal =
            host === "localhost" ||
            host === "127.0.0.1" ||
            /^192\.168\./.test(host) ||
            /^10\./.test(host) ||
            host === "";

        if (estLocal) {
            alert(
                "La traduction automatique en wolof necessite que le site soit " +
                "publie en ligne (Google Translate ne peut pas traduire un site " +
                "local en cours de developpement)."
            );
            return;
        }

        // URL de traduction via le proxy Google Translate
        // Format : https://<host-avec-tirets>.translate.goog/<path>?_x_tr_sl=fr&_x_tr_tl=wo
        var proxyHost = host.replace(/-/g, "--").replace(/\./g, "-") + ".translate.goog";
        var path = window.location.pathname + window.location.search;
        var proxyUrl = window.location.protocol + "//" + proxyHost + path;

        // Ajouter les paramètres de traduction
        var sep = proxyUrl.indexOf("?") === -1 ? "?" : "&";
        proxyUrl += sep + "_x_tr_sl=fr&_x_tr_tl=wo&_x_tr_hl=fr";

        window.open(proxyUrl, "_blank", "noopener");
    }


    // -----------------------------------------------------------------
    // Construire le widget
    // -----------------------------------------------------------------
    function buildWidget() {
        var wrapper = document.createElement("div");
        wrapper.innerHTML = [
            '<button type="button" class="a11y-button" id="a11yOpen" ',
            '        aria-label="Ouvrir le menu d\'accessibilite" ',
            '        title="Accessibilite">',
            '  <i class="bi bi-universal-access-circle"></i>',
            '</button>',
            '<div class="a11y-panel" id="a11yPanel" role="dialog" aria-label="Options d\'accessibilite">',
            '  <div class="a11y-panel-header">',
            '    <h3 class="a11y-panel-title">',
            '      <i class="bi bi-universal-access-circle me-1"></i> Accessibilite',
            '    </h3>',
            '    <button type="button" class="a11y-panel-close" id="a11yClose" aria-label="Fermer">&times;</button>',
            '  </div>',

            '  <div class="a11y-group">',
            '    <span class="a11y-group-label">Taille du texte</span>',
            '    <div class="a11y-btn-row">',
            '      <button type="button" class="a11y-btn" data-action="smaller" aria-label="Reduire le texte">',
            '        <span style="font-size:12px">A</span>-',
            '      </button>',
            '      <button type="button" class="a11y-btn" data-action="bigger" aria-label="Agrandir le texte">',
            '        <span style="font-size:18px">A</span>+',
            '      </button>',
            '    </div>',
            '  </div>',

            '  <div class="a11y-group">',
            '    <span class="a11y-group-label">Affichage</span>',
            '    <div class="a11y-btn-row">',
            '      <button type="button" class="a11y-btn" data-action="contrast" aria-label="Basculer le contraste eleve">',
            '        <i class="bi bi-circle-half"></i> Contraste',
            '      </button>',
            '      <button type="button" class="a11y-btn" data-action="grayscale" aria-label="Basculer les niveaux de gris">',
            '        <i class="bi bi-droplet"></i> N&B',
            '      </button>',
            '    </div>',
            '  </div>',

            '  <div class="a11y-group">',
            '    <span class="a11y-group-label">Lecture vocale</span>',
            '    <div class="a11y-btn-row">',
            '      <button type="button" class="a11y-btn" data-action="speak" style="flex:1" aria-label="Lire la page a haute voix">',
            '        <i class="bi bi-volume-up"></i> Lire',
            '      </button>',
            '    </div>',
            '  </div>',

            '  <div class="a11y-group">',
            '    <span class="a11y-group-label">Langue</span>',
            '    <div class="a11y-btn-row">',
            '      <button type="button" class="a11y-btn" data-action="wolof" style="flex:1" aria-label="Traduire la page en Wolof">',
            '        <i class="bi bi-translate"></i> Traduire en Wolof',
            '      </button>',
            '    </div>',
            '  </div>',

            '  <button type="button" class="a11y-btn a11y-reset" data-action="reset">',
            '    <i class="bi bi-arrow-counterclockwise"></i> Reinitialiser',
            '  </button>',
            '</div>'
        ].join("");
        document.body.appendChild(wrapper);
    }

    // -----------------------------------------------------------------
    // Brancher les evenements
    // -----------------------------------------------------------------
    function bindEvents() {
        var openBtn = document.getElementById("a11yOpen");
        var closeBtn = document.getElementById("a11yClose");
        var panel = document.getElementById("a11yPanel");

        openBtn.addEventListener("click", function () {
            panel.classList.toggle("open");
        });

        closeBtn.addEventListener("click", function () {
            panel.classList.remove("open");
        });

        // Fermer si on clique en dehors
        document.addEventListener("click", function (e) {
            if (!panel.contains(e.target) && !openBtn.contains(e.target)) {
                panel.classList.remove("open");
            }
        });

        // Actions des boutons
        panel.querySelectorAll(".a11y-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var action = btn.dataset.action;
                var prefs = loadPrefs();

                switch (action) {
                    case "bigger":
                        prefs.fontStep = Math.min(6, prefs.fontStep + 1);
                        break;
                    case "smaller":
                        prefs.fontStep = Math.max(-2, prefs.fontStep - 1);
                        break;
                    case "contrast":
                        prefs.contrast = !prefs.contrast;
                        break;
                    case "grayscale":
                        prefs.grayscale = !prefs.grayscale;
                        break;
                    case "speak":
                        if (currentUtterance) {
                            stopSpeaking();
                        } else {
                            speakPage();
                        }
                        return; // pas de sauvegarde
                    case "wolof":
                        translateToWolof();
                        return; // pas de sauvegarde
                    case "reset":
                        prefs = Object.assign({}, DEFAULT);
                        stopSpeaking();
                        break;
                }

                savePrefs(prefs);
                applyPrefs(prefs);
            });
        });

        // Stopper la lecture quand on quitte la page
        window.addEventListener("beforeunload", stopSpeaking);
    }

    // -----------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------
    function init() {
        buildWidget();
        bindEvents();
        applyPrefs(loadPrefs());
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
