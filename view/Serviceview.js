// ============================================================
//  VIEW: ServiceView.js
//  Renders the Printing Services card grid
// ============================================================

const ServiceView = (() => {

    function render(services, onCardClick) {
        const grid = document.getElementById('servicesGrid');
        if (!grid) return;
        grid.innerHTML = '';
        services.forEach(svc => {
            const card = document.createElement('div');
            card.className = 'service-card';
            card.dataset.id = svc.id;
            card.innerHTML = `
        <div class="service-card-icon">${svc.icon}</div>
        <div class="service-card-label">${svc.label}</div>
      `;
            card.addEventListener('click', () => onCardClick(svc.id));
            grid.appendChild(card);
        });
    }

    return { render };
})();