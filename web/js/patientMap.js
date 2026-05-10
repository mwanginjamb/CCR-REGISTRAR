/**
 * patientMap.js
 * Renders a single-pin Leaflet map for the patient readonly view.
 * Expects window.PatientMapConfig to be set before this script runs.
 *
 * Config shape:
 *   {
 *     lat:      number,
 *     lng:      number,
 *     label:    string,
 *     accuracy: number   // metres, 0 if unknown
 *   }
 */
(function () {

    const cfg = window.PatientMapConfig;

    if (!cfg || !cfg.lat || !cfg.lng) {
        console.warn('patientMap.js: no config found, aborting.');
        return;
    }

    // ── Custom SVG pin icon ───────────────────────────────────────────────────
    function makePatientIcon(label) {
        const color = '#001A48';

        const svg = `
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="42" viewBox="0 0 32 42">
                <circle cx="16" cy="16" r="14" fill="${color}" opacity="0.15"/>
                <circle cx="16" cy="16" r="10" fill="${color}"/>
                <circle cx="16" cy="16" r="4"  fill="#ffffff"/>
                <line x1="16" y1="26" x2="16" y2="40" stroke="${color}" stroke-width="2" opacity="0.5"/>
            </svg>`;

        return L.divIcon({
            html: `<div style="position:relative">
                ${svg}
                <div style="
                    position: absolute;
                    top: -20px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #001A48;
                    color: #ffffff;
                    font-size: 9px;
                    font-family: system-ui, sans-serif;
                    font-weight: 600;
                    padding: 2px 6px;
                    border-radius: 3px;
                    white-space: nowrap;
                    max-width: 120px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                ">${label}</div>
            </div>`,
            className: '',
            iconSize: [32, 42],
            iconAnchor: [16, 40],
            popupAnchor: [0, -44]
        });
    }

    // ── Init map ──────────────────────────────────────────────────────────────
    const map = L.map('patient-map', {
        zoomControl: true,
        scrollWheelZoom: false,
        attributionControl: true
    }).setView([cfg.lat, cfg.lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // ── Marker ────────────────────────────────────────────────────────────────
    const marker = L.marker([cfg.lat, cfg.lng], {
        icon: makePatientIcon(cfg.label)
    }).addTo(map);

    // ── Popup ─────────────────────────────────────────────────────────────────
    marker.bindPopup(`
        <div style="font-family:system-ui,sans-serif;min-width:160px">
            <div style="font-weight:700;font-size:.85rem;margin-bottom:4px;color:#001A48">
                ${cfg.label}
            </div>
            <div style="font-size:.7rem;color:#555;margin-bottom:2px">
                📍 ${cfg.lat}, ${cfg.lng}
            </div>
            ${cfg.accuracy ? `<div style="font-size:.68rem;color:#888">Accuracy: ±${cfg.accuracy}m</div>` : ''}
        </div>
    `).openPopup();

    // ── Accuracy circle ───────────────────────────────────────────────────────
    if (cfg.accuracy > 0) {
        L.circle([cfg.lat, cfg.lng], {
            radius: cfg.accuracy,
            color: '#001A48',
            fillColor: '#001A48',
            fillOpacity: 0.08,
            weight: 1,
            dashArray: '4 4'
        }).addTo(map);
    }

    // ── Tile filter ───────────────────────────────────────────────────────────
    const style = document.createElement('style');
    style.textContent = '#patient-map .leaflet-tile-pane { filter: saturate(0.7) brightness(1.0); }';
    document.head.appendChild(style);

})();