/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './views/**/*.php',
        './widgets/**/*.php',
        './web/js/**/*.js',
    ],
    darkMode: "class",
    theme: {
        extend: {
            "colors": {
                "inverse-surface": "#2d3133",
                "on-surface-variant": "#444651",
                "primary-fixed": "#dae2ff",
                "surface-container-highest": "#e0e3e5",
                "surface-dim": "#d8dadc",
                "on-error": "#ffffff",
                "surface-container-high": "#e6e8ea",
                "background": "#f7f9fb",
                "on-tertiary": "#ffffff",
                "surface-container-lowest": "#ffffff",
                "error": "#ba1a1a",
                "on-secondary-fixed": "#001f2a",
                "surface-bright": "#f7f9fb",
                "primary": "#001a48",
                "secondary": "#0d6683",
                "on-primary-fixed": "#001946",
                "primary-container": "#002d72",
                "surface-variant": "#e0e3e5",
                "on-primary-fixed-variant": "#224489",
                "on-error-container": "#93000a",
                "outline": "#747782",
                "on-secondary-container": "#056380",
                "on-primary-container": "#7a97e2",
                "outline-variant": "#c4c6d2",
                "inverse-on-surface": "#eff1f3",
                "primary-fixed-dim": "#b1c5ff",
                "tertiary-container": "#5d1b02",
                "surface-container": "#eceef0",
                "surface": "#f7f9fb",
                "on-tertiary-container": "#e17f5d",
                "surface-tint": "#3d5ca2",
                "error-container": "#ffdad6",
                "on-tertiary-fixed-variant": "#7a3014",
                "surface-container-low": "#f2f4f6",
                "on-tertiary-fixed": "#390c00",
                "on-secondary-fixed-variant": "#004d65",
                "secondary-fixed": "#bee9ff",
                "secondary-fixed-dim": "#8ad0f1",
                "inverse-primary": "#b1c5ff",
                "tertiary": "#3b0d00",
                "on-surface": "#191c1e",
                "on-primary": "#ffffff",
                "secondary-container": "#98deff",
                "tertiary-fixed-dim": "#ffb59c",
                "on-background": "#191c1e",
                "tertiary-fixed": "#ffdbd0",
                "on-secondary": "#ffffff"
            },
            "borderRadius": {
                "DEFAULT": "0.125rem",
                "lg": "0.25rem",
                "xl": "0.5rem",
                "full": "0.75rem"
            },
            "fontFamily": {
                "headline": ["Manrope"],
                "body": ["Inter"],
                "label": ["Inter"]
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/container-queries'),
    ],
}

