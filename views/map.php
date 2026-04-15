<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Carte interactive – Cameroun</title>

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <!-- Feuille de style personnalisée -->
  <link rel="stylesheet" href="style.css" />
  <style>
            /* ===========================
        RESET & BASE
        =========================== */
        *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        }

        body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f0f2f5;
        color: #1a1a2e;
        min-height: 100vh;
        padding: 24px 16px;
        }

        /* ===========================
        WRAPPER PRINCIPAL
        =========================== */
        .app-wrapper {
        max-width: 960px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        }

        /* ===========================
        HEADER
        =========================== */
        .app-header {
        background: linear-gradient(135deg, #007A5E 0%, #009B6F 50%, #CE1126 100%);
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        }

        .header-left {
        display: flex;
        align-items: center;
        gap: 14px;
        }

        .flag {
        font-size: 32px;
        line-height: 1;
        }

        .app-header h1 {
        color: #ffffff;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 2px;
        }

        .app-header .subtitle {
        color: rgba(255,255,255,0.8);
        font-size: 13px;
        }

        /* ===========================
        RECHERCHE
        =========================== */
        .search-section {
        padding: 16px 24px 12px;
        border-bottom: 1px solid #eef0f3;
        }

        .search-wrapper {
        display: flex;
        gap: 10px;
        position: relative;
        }

        #search-input {
        flex: 1;
        padding: 10px 16px;
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        font-size: 14px;
        color: #1a1a2e;
        background: #ffffff;
        outline: none;
        transition: border-color 0.2s;
        }

        #search-input:focus {
        border-color: #007A5E;
        box-shadow: 0 0 0 3px rgba(0, 122, 94, 0.12);
        }

        #search-input::placeholder {
        color: #9ca3af;
        }

        #search-btn {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 10px 18px;
        background: #007A5E;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        white-space: nowrap;
        }

        #search-btn:hover {
        background: #005f48;
        }

        #search-btn:active {
        transform: scale(0.97);
        }

        /* Suggestions dropdown */
        #suggestions {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 90px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.10);
        z-index: 9999;
        display: none;
        overflow: hidden;
        }

        .suggestion-item {
        padding: 10px 16px;
        font-size: 13px;
        color: #374151;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
        display: flex;
        align-items: center;
        gap: 8px;
        }

        .suggestion-item:last-child {
        border-bottom: none;
        }

        .suggestion-item::before {
        content: "📍";
        font-size: 13px;
        flex-shrink: 0;
        }

        .suggestion-item:hover {
        background: #f0fdf9;
        color: #007A5E;
        }

        /* ===========================
        CARTE
        =========================== */
        #map {
        width: 100%;
        height: 480px;
        z-index: 1;
        }

        /* ===========================
        PANNEAU COORDONNÉES
        =========================== */
        .coords-panel {
        display: flex;
        align-items: center;
        gap: 0;
        padding: 14px 24px;
        background: #f8fafc;
        border-top: 1px solid #eef0f3;
        flex-wrap: wrap;
        gap: 8px;
        }

        .coord-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 0 16px 0 0;
        }

        .coord-item:first-child {
        padding-left: 0;
        }

        .coord-place {
        flex: 1;
        }

        .coord-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #9ca3af;
        }

        .coord-value {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a2e;
        font-family: 'SFMono-Regular', 'Courier New', monospace;
        }

        .coord-value.place-name {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        }

        .divider {
        width: 1px;
        height: 36px;
        background: #e5e7eb;
        margin-right: 16px;
        flex-shrink: 0;
        }

        .copy-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        background: #ffffff;
        color: #374151;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        margin-left: auto;
        }

        .copy-btn:hover {
        border-color: #007A5E;
        color: #007A5E;
        background: #f0fdf9;
        }

        .copy-btn.copied {
        border-color: #007A5E;
        background: #007A5E;
        color: #ffffff;
        }

        /* ===========================
        MESSAGE DE STATUT
        =========================== */
        .status-msg {
        padding: 10px 24px 16px;
        font-size: 12px;
        color: #9ca3af;
        background: #f8fafc;
        border-top: 1px solid #eef0f3;
        text-align: center;
        }

        /* ===========================
        POPUP LEAFLET CUSTOM
        =========================== */
        .leaflet-popup-content-wrapper {
        border-radius: 10px !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
        }

        .leaflet-popup-content {
        font-size: 13px;
        line-height: 1.6;
        color: #1a1a2e;
        }

        .leaflet-popup-content b {
        color: #007A5E;
        display: block;
        margin-bottom: 2px;
        }

        /* ===========================
        RESPONSIVE
        =========================== */
        @media (max-width: 600px) {
        body {
            padding: 0;
        }

        .app-wrapper {
            border-radius: 0;
        }

        .app-header {
            padding: 16px;
        }

        .app-header h1 {
            font-size: 17px;
        }

        .search-section {
            padding: 12px 16px;
        }

        .search-wrapper {
            flex-direction: column;
        }

        #suggestions {
            right: 0;
        }

        #search-btn {
            justify-content: center;
        }

        #map {
            height: 360px;
        }

        .coords-panel {
            padding: 12px 16px;
        }

        .divider {
            display: none;
        }

        .coord-item {
            padding-right: 8px;
        }

        .copy-btn {
            width: 100%;
            justify-content: center;
        }
        }
  </style>
</head>
<body>

  <div class="app-wrapper">

    <header class="app-header">
      <div class="header-left">
        <div class="flag">🇨🇲</div>
        <div>
          <h1>Carte du Cameroun</h1>
          <p class="subtitle">Recherche de lieux · Marquage · Coordonnées GPS</p>
        </div>
      </div>
    </header>

    <div class="search-section">
      <div class="search-wrapper">
        <input
          type="text"
          id="search-input"
          placeholder="Rechercher un lieu au Cameroun (ex : Yaoundé, Kribi, Bafoussam…)"
          autocomplete="off"
        />
        <button id="search-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          Rechercher
        </button>
        <div id="suggestions"></div>
      </div>
    </div>

    <div id="map"></div>

    <div class="coords-panel" id="coords-panel">
      <div class="coord-item">
        <span class="coord-label">Latitude</span>
        <span class="coord-value" id="lat-val">—</span>
      </div>
      <div class="divider"></div>
      <div class="coord-item">
        <span class="coord-label">Longitude</span>
        <span class="coord-value" id="lng-val">—</span>
      </div>
      <div class="divider"></div>
      <div class="coord-item coord-place">
        <span class="coord-label">Lieu identifié</span>
        <span class="coord-value place-name" id="place-val">—</span>
      </div>
      <button class="copy-btn" id="copy-btn" style="display:none">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </svg>
        Copier
      </button>
    </div>

    <p class="status-msg" id="status-msg">
      Cliquez sur la carte ou recherchez un lieu pour afficher les coordonnées GPS.
    </p>

  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <!-- Script principal -->
  <script>
            /**
         * app.js — Carte interactive Cameroun
         * Leaflet + OpenStreetMap + Nominatim (via proxy PHP)
         */

        // ===========================
        // CONFIGURATION
        // ===========================

        const CONFIG = {
        // Limites géographiques du Cameroun [SW, NE]
        bounds: [[1.65, 8.4], [13.08, 16.19]],
        // Centre initial
        center: [5.5, 12.3],
        zoom: 6,
        minZoom: 5,
        maxZoom: 18,
        // URL du proxy PHP (même dossier)
        proxyUrl: '../models/proxy.php',
        };

        // ===========================
        // INITIALISATION DE LA CARTE
        // ===========================

        const map = L.map('map', {
        center: CONFIG.center,
        zoom: CONFIG.zoom,
        minZoom: CONFIG.minZoom,
        maxZoom: CONFIG.maxZoom,
        maxBounds: CONFIG.bounds,
        maxBoundsViscosity: 1.0,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        }).addTo(map);

        // ===========================
        // ICÔNE MARQUEUR PERSONNALISÉE
        // ===========================

        const markerIcon = L.divIcon({
        className: '',
        html: `
            <p>o</p>`,
        iconSize: [32, 42],
        iconAnchor: [16, 42],
        popupAnchor: [0, -44],
        });

        // ===========================
        // GESTION DU MARQUEUR
        // ===========================

        let marker = null;

        function placeMarker(lat, lng, placeName) {
        if (marker) map.removeLayer(marker);

        marker = L.marker([lat, lng], {
            icon: markerIcon,
            draggable: true,
        }).addTo(map);

        const popupContent = buildPopupContent(lat, lng, placeName);
        marker.bindPopup(popupContent).openPopup();

        updateCoordsPanel(lat, lng, placeName || '—');

        // Déplacement du marqueur à la souris
        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            const lat2 = pos.lat;
            const lng2 = pos.lng;
            reverseGeocode(lat2, lng2, function (name) {
            marker.setPopupContent(buildPopupContent(lat2, lng2, name)).openPopup();
            updateCoordsPanel(lat2, lng2, name);
            });
            setStatus('Marqueur déplacé. Géolocalisation en cours…');
        });
        }

        function buildPopupContent(lat, lng, name) {
        return `
            <b>${name || 'Lieu marqué'}</b>
            Lat : ${lat.toFixed(6)}<br>
            Lng : ${lng.toFixed(6)}
        `;
        }

        // ===========================
        // MISE À JOUR DU PANNEAU
        // ===========================

        function updateCoordsPanel(lat, lng, place) {
        document.getElementById('lat-val').textContent = lat.toFixed(6);
        document.getElementById('lng-val').textContent = lng.toFixed(6);
        document.getElementById('place-val').textContent = place || '—';

        const copyBtn = document.getElementById('copy-btn');
        copyBtn.style.display = 'flex';
        copyBtn.classList.remove('copied');
        copyBtn.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="9" y="9" width="13" height="13" rx="2"/>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
            Copier`;
        }

        function setStatus(msg) {
        document.getElementById('status-msg').textContent = msg;
        }

        // ===========================
        // CLIC SUR LA CARTE
        // ===========================

        map.on('click', function (e) {
        const { lat, lng } = e.latlng;
        placeMarker(lat, lng, null);
        setStatus('Géolocalisation inverse en cours…');
        reverseGeocode(lat, lng, function (name) {
            if (marker) {
            marker.setPopupContent(buildPopupContent(lat, lng, name)).openPopup();
            updateCoordsPanel(lat, lng, name);
            }
            setStatus('Cliquez sur la carte ou recherchez un lieu pour afficher les coordonnées GPS.');
        });
        });

        // ===========================
        // GÉOCODAGE INVERSE (proxy PHP)
        // ===========================

        function reverseGeocode(lat, lng, callback) {
        const url = `${CONFIG.proxyUrl}?action=reverse&lat=${lat}&lon=${lng}`;
        fetch(url)
            .then(r => r.json())
            .then(data => {
            if (data && data.display_name) {
                const short = formatPlaceName(data.display_name);
                callback(short);
            } else {
                callback('Lieu inconnu');
            }
            })
            .catch(() => callback('Erreur de géolocalisation'));
        }

        // ===========================
        // RECHERCHE (proxy PHP)
        // ===========================

        const searchInput = document.getElementById('search-input');
        const suggestionsBox = document.getElementById('suggestions');
        let debounceTimer = null;

        searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (q.length < 3) {
            hideSuggestions();
            return;
        }
        debounceTimer = setTimeout(() => fetchSuggestions(q), 400);
        });

        searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            clearTimeout(debounceTimer);
            doSearch(this.value.trim());
            hideSuggestions();
        }
        if (e.key === 'Escape') hideSuggestions();
        });

        document.getElementById('search-btn').addEventListener('click', function () {
        clearTimeout(debounceTimer);
        doSearch(searchInput.value.trim());
        hideSuggestions();
        });

        function fetchSuggestions(query) {
        const url = `${CONFIG.proxyUrl}?action=search&q=${encodeURIComponent(query)}&limit=5`;
        fetch(url)
            .then(r => r.json())
            .then(results => {
            suggestionsBox.innerHTML = '';
            if (!results.length) { hideSuggestions(); return; }

            results.forEach(item => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = formatPlaceName(item.display_name);
                div.addEventListener('click', () => {
                const lat = parseFloat(item.lat);
                const lng = parseFloat(item.lon);
                const name = formatPlaceName(item.display_name);
                map.setView([lat, lng], 13, { animate: true });
                placeMarker(lat, lng, name);
                searchInput.value = name;
                hideSuggestions();
                setStatus('Lieu trouvé et marqué.');
                });
                suggestionsBox.appendChild(div);
            });

            suggestionsBox.style.display = 'block';
            })
            .catch(() => hideSuggestions());
        }

        function doSearch(query) {
        if (!query) return;
        setStatus('Recherche en cours…');
        const url = `${CONFIG.proxyUrl}?action=search&q=${encodeURIComponent(query)}&limit=1`;
        fetch(url)
            .then(r => r.json())
            .then(results => {
            if (!results.length) {
                setStatus('Aucun résultat trouvé au Cameroun pour : "' + query + '"');
                return;
            }
            const item = results[0];
            const lat = parseFloat(item.lat);
            const lng = parseFloat(item.lon);
            const name = formatPlaceName(item.display_name);
            map.setView([lat, lng], 13, { animate: true });
            placeMarker(lat, lng, name);
            searchInput.value = name;
            setStatus('Lieu trouvé et marqué sur la carte.');
            })
            .catch(() => setStatus('Erreur lors de la recherche.'));
        }

        // ===========================
        // UTILITAIRES
        // ===========================

        function formatPlaceName(displayName) {
        // Garde les 3 premiers segments (ville, région, pays)
        return displayName.split(',').slice(0, 3).join(', ').trim();
        }

        function hideSuggestions() {
        suggestionsBox.style.display = 'none';
        }

        // Fermer suggestions si clic ailleurs
        document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-wrapper')) hideSuggestions();
        });

        // ===========================
        // BOUTON COPIER
        // ===========================

        document.getElementById('copy-btn').addEventListener('click', function () {
        const lat = document.getElementById('lat-val').textContent;
        const lng = document.getElementById('lng-val').textContent;
        const place = document.getElementById('place-val').textContent;
        const text = `Lieu : ${place}\nLatitude : ${lat}\nLongitude : ${lng}`;

        navigator.clipboard.writeText(text).then(() => {
            this.classList.add('copied');
            this.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            Copié !`;
            setTimeout(() => {
            this.classList.remove('copied');
            this.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="9" y="9" width="13" height="13" rx="2"/>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
                Copier`;
            }, 2000);
        }).catch(() => {
            alert(`Coordonnées :\n${text}`);
        });
        });
  </script>

</body>
</html>