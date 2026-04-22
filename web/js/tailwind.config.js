window.tailwind = window.tailwind || {};
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: "#001a48",
                secondary: "#0d6683",
                background: "#f7f9fb",
                surface: "#f7f9fb",
                "surface-container-low": "#f2f4f6",
                "surface-container-lowest": "#ffffff",
                "surface-container": "#eceef0",
                "surface-variant": "#e0e3e5",
                "on-surface": "#191c1e",
                "on-surface-variant": "#444651",
                outline: "#747782",
                "outline-variant": "#c4c6d2",
                "primary-container": "#002d72",
                "secondary-container": "#98deff",
                "on-primary": "#ffffff",
                "on-primary-container": "#7a97e2",
                "on-secondary-container": "#056380",
            },
            borderRadius: {
                DEFAULT: "0.125rem",
                lg: "0.25rem",
                xl: "0.5rem",
                full: "0.75rem"
            }
        }
    }
}