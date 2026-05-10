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

    /** Inject lat/lng as hidden inputs so they travel with the POST if needed */
    const injectIntoForm = (formEl) => {
        if (!_coords) return;
        ['lat', 'lng', 'accuracy', 'captured_at'].forEach(key => {
            let input = formEl.querySelector(`input[name="geo_${key}"]`);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = `geo_${key}`;
                formEl.appendChild(input);
            }
            input.value = _coords[key];
        });
    };

    return { capture, getCoords, injectIntoForm };
})();