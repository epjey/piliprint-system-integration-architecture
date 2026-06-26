<script>
    // ============================================================
    //  ServiceView.php
    //  JavaScript served as a PHP file.
    //  When using a PHP server, add header('Content-Type: application/javascript') above.
    // ============================================================
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
                
                let iconHtml = '';
                if (svc.icon && svc.icon.trim() !== '') {
                    iconHtml = `<img src="${svc.icon}" alt="${svc.label}" style="width: 100%; height: 100%; object-fit: contain;">`;
                } else {
                    iconHtml = ServiceModel.placeholderIcon(svc.label);
                }

                card.innerHTML = `
        <div class="service-card-icon">${iconHtml}</div>
        <div class="service-card-label">${svc.label}</div>
      `;
                card.addEventListener('click', () => onCardClick(svc.id));
                grid.appendChild(card);
            });
        }

        return { render };
    })();

</script>