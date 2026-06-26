<script>
// ============================================================
// ServiceModel.php
// JavaScript served as a PHP file.
// ============================================================
// ============================================================
// MODEL: ServiceModel.js
// Holds all printing service definitions, variants, options
// ============================================================

const ServiceModel = (() => {

    const SHOP = {
        name: 'PILI PRINT SHOP',
        tagline: 'Professional Printing Services',
        address: '123 NCST COLLEGE., DASMA, PH',
        tel: 'Tel: (02) 8123-4567',
    };

    let services = [];

    // Fetch services from database API
    async function loadServices(includeArchived = false) {
        try {
            const url = `api/services.php${includeArchived ? '?include_archived=1' : ''}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.success) {
                services = data.services;
            } else {
                console.error('Failed to load services:', data.message);
                // Fallback to empty if fail
                services = [];
            }
        } catch (e) {
            console.error('Error loading services:', e);
            services = [];
        }
    }

    // Helper: placeholder icon if none provided
    const placeholderIcon = (label) => `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
        <rect x="10" y="8" width="40" height="44" rx="3" fill="#e9ecef" stroke="#c8d4e3" stroke-width="1.5" />
        <text x="30" y="36" text-anchor="middle" font-size="8" fill="#999" font-family="Arial">${label || ''}</text>
    </svg>`;

    return {
        getShopInfo: () => SHOP,
        loadServices,
        getAll: () => services,
        getById: (id) => services.find(s => s.id == id),
        placeholderIcon
    };
})();
</script>
