/**
 * GeoTag — captures coordinates and injects them into hidden form fields.
 * Falls back gracefully if user denies permission.
 */
const GeoTag = (() => {
    let _coords = null;

    const capture = () => new Promise((resolve) => {
        if (!navigator.geolocation) return resolve(null);

        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                _coords = {
                    lat: coords.latitude,
                    lng: coords.longitude,
                    accuracy: coords.accuracy,
                    captured_at: new Date().toISOString()
                };
                resolve(_coords);
            },
            (err) => {
                console.warn('GeoTag: permission denied or unavailable', err.message);
                resolve(null); // non-blocking — form still submits
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    });

    const getCoords = () => _coords;

    //   fillDisplay helper - injects coords into form fields
    const fillDisplay = (coords) => {
        const badge = document.getElementById('geo-status-badge');

        if (!coords) {
            if (badge) {
                badge.textContent = 'Unavailable';
                badge.className = badge.className.replace('text-outline', 'text-error');
            }
            return;
        }

        ['lat', 'lng', 'accuracy', 'captured_at'].forEach(key => {
            // hidden input for POST
            const hidden = document.getElementById(`geo_${key}`);
            if (hidden) hidden.value = coords[key];

            // visible display input
            const display = document.getElementById(`geo-display-${key}`);
            if (display) {
                display.value = key === 'captured_at'
                    ? new Date(coords[key]).toLocaleTimeString()
                    : (typeof coords[key] === 'number' ? coords[key].toFixed(5) : coords[key]);
            }
        });

        if (badge) {
            badge.textContent = 'Captured ✓';
            badge.className = badge.className
                .replace('bg-surface-container-high', 'bg-tertiary-container')
                .replace('text-outline', 'text-on-tertiary-container');
        }
    };

    return { capture, getCoords, fillDisplay };
})();