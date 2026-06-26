<script>
// ============================================================
//  ModalView.php
//  JavaScript served as a PHP file.
//  When using a PHP server, add header('Content-Type: application/javascript') above.
// ============================================================
// ============================================================
//  VIEW: ModalView.js
//  Renders the 3-step "Add Item to Order" modal
// ============================================================

const ModalView = (() => {

    // State held within the view for the active modal session
    let _state = null;

    function open(service, onComplete) {
        _state = {
            service,
            step: 1,
            selectedVariant: null,
            selectedOptions: {},   // groupId -> { optionId, optionLabel, price, groupLabel }
            qty: 1,
            onComplete,
        };
        _renderStep();
        const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
        modal.show();
        _state.modal = modal;
    }

    function _getUnitPrice() {
        if (!_state.selectedVariant) return 0;
        let total = _state.selectedVariant.price;
        Object.values(_state.selectedOptions).forEach(o => { total += o.price || 0; });
        return total;
    }

    function _getItemTotal() {
        return _getUnitPrice() * (_state.qty || 1);
    }

    function _updateItemTotal() {
        const el = document.getElementById('modalItemTotal');
        if (el) el.textContent = '₱' + (_getItemTotal()).toFixed(2);
    }

    function _setStep(n) {
        for (let i = 1; i <= 3; i++) {
            const circle = document.getElementById('stepCircle' + i);
            const line = document.getElementById('stepLine' + (i < 3 ? i : null));
            if (circle) {
                circle.classList.remove('active', 'done');
                if (i < n) circle.classList.add('done');
                if (i === n) circle.classList.add('active');
            }
        }
        // Step lines
        for (let i = 1; i <= 2; i++) {
            const line = document.getElementById('stepLine' + i);
            if (line) {
                line.classList.toggle('active', i < n);
            }
        }
    }

    function _renderStep() {
        _setStep(_state.step);
        const body = document.getElementById('modalBody');
        const backBtn = document.getElementById('modalBackBtn');
        const nextBtn = document.getElementById('modalNextBtn');
        if (!body) return;

        if (_state.step === 1) {
            // -- VARIANT SELECT --
            body.innerHTML = `
        <div class="select-section-title">SELECT VARIANT</div>
        <div class="variant-grid" id="variantGrid"></div>
      `;
            const grid = document.getElementById('variantGrid');
            _state.service.variants.forEach(v => {
                const card = document.createElement('div');
                card.className = 'variant-card' + (_state.selectedVariant && _state.selectedVariant.id === v.id ? ' selected' : '');
                card.textContent = v.label + (v.price > 0 ? '  ₱' + v.price.toFixed(2) : '  ₱' + v.price.toFixed(2));
                card.addEventListener('click', () => {
                    _state.selectedVariant = v;
                    // reset options
                    _state.selectedOptions = {};
                    document.querySelectorAll('.variant-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    _updateItemTotal();
                });
                grid.appendChild(card);
            });

            backBtn.textContent = 'Cancel';
            backBtn.onclick = () => _state.modal.hide();
            nextBtn.textContent = 'Next Step →';
            nextBtn.className = 'btn btn-modal-next';
            nextBtn.onclick = () => {
                if (!_state.selectedVariant) {
                    Swal.fire({ icon: 'warning', title: 'Select a variant', text: 'Please select a variant to continue.', confirmButtonColor: '#2e4a6e' });
                    return;
                }
                if (_state.service.optionGroups && _state.service.optionGroups.length > 0) {
                    _state.step = 2;
                } else {
                    _state.step = 3;
                }
                _renderStep();
            };

        } else if (_state.step === 2) {
            // -- OPTIONS SELECT --
            const groups = _state.service.optionGroups || [];
            let html = '';
            groups.forEach((g, gi) => {
                html += `<div class="select-section-title">${g.label.toUpperCase()}</div>`;
                html += `<div class="option-group" id="optGroup${gi}">`;
                g.options.forEach(opt => {
                    const sel = _state.selectedOptions[g.id] && _state.selectedOptions[g.id].optionId === opt.id ? ' selected' : '';
                    html += `<div class="option-card${sel}" data-gid="${g.id}" data-gi="${gi}" data-optid="${opt.id}" data-price="${opt.price}" data-glabel="${g.label}" data-olabel="${opt.label}">
            ${opt.label}${opt.price > 0 ? ' + ₱' + opt.price : ''}
          </div>`;
                });
                html += '</div>';
            });
            body.innerHTML = html;

            // Pre-select first option per group if none chosen
            groups.forEach(g => {
                if (!_state.selectedOptions[g.id]) {
                    const firstOpt = g.options[0];
                    if (firstOpt) {
                        _state.selectedOptions[g.id] = { optionId: firstOpt.id, optionLabel: firstOpt.label, price: firstOpt.price, groupLabel: g.label };
                        body.querySelector(`[data-gid="${g.id}"][data-optid="${firstOpt.id}"]`)?.classList.add('selected');
                    }
                }
            });

            body.querySelectorAll('.option-card').forEach(card => {
                card.addEventListener('click', () => {
                    const gid = card.dataset.gid;
                    body.querySelectorAll(`[data-gid="${gid}"]`).forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    _state.selectedOptions[gid] = {
                        optionId: card.dataset.optid,
                        optionLabel: card.dataset.olabel,
                        price: parseFloat(card.dataset.price) || 0,
                        groupLabel: card.dataset.glabel,
                    };
                    _updateItemTotal();
                });
            });

            backBtn.textContent = '← Back';
            backBtn.onclick = () => { _state.step = 1; _renderStep(); };
            nextBtn.textContent = 'Next Step →';
            nextBtn.className = 'btn btn-modal-next';
            nextBtn.onclick = () => { _state.step = 3; _renderStep(); };

        } else if (_state.step === 3) {
            // -- QTY --
            const optKeys = Object.values(_state.selectedOptions).map(o => o.optionLabel).join(' · ');
            body.innerHTML = `
        <div class="qty-summary-box">
          <div class="qty-summary-title">${_state.service.label} — ${_state.selectedVariant.label}</div>
          <div class="qty-summary-sub">Base ₱${_state.selectedVariant.price.toFixed(2)} · ${optKeys || 'No options'}</div>
        </div>
        <div class="qty-counter">
          <button class="qty-btn minus" id="qtyMinus">−</button>
          <input type="number" class="qty-display" id="qtyInput" min="1" value="${_state.qty}" />
          <button class="qty-btn plus" id="qtyPlus">+</button>
        </div>
        <div class="qty-unit">copies / sets</div>
      `;

            const qtyInput = document.getElementById('qtyInput');

            document.getElementById('qtyMinus').addEventListener('click', () => {
                let val = parseInt(qtyInput.value) || 1;
                if (val > 1) {
                    val--;
                    qtyInput.value = val;
                    _state.qty = val;
                    _updateItemTotal();
                }
            });
            document.getElementById('qtyPlus').addEventListener('click', () => {
                let val = parseInt(qtyInput.value) || 1;
                val++;
                qtyInput.value = val;
                _state.qty = val;
                _updateItemTotal();
            });
            qtyInput.addEventListener('input', () => {
                let val = parseInt(qtyInput.value);
                if (!isNaN(val) && val >= 1) {
                    _state.qty = val;
                    _updateItemTotal();
                }
            });
            qtyInput.addEventListener('blur', () => {
                let val = parseInt(qtyInput.value);
                if (isNaN(val) || val < 1) {
                    val = 1;
                    qtyInput.value = 1;
                }
                _state.qty = val;
                _updateItemTotal();
            });

            backBtn.textContent = '← Back';
            backBtn.onclick = () => {
                _state.step = _state.service.optionGroups && _state.service.optionGroups.length > 0 ? 2 : 1;
                _renderStep();
            };
            nextBtn.textContent = 'Add to Order';
            nextBtn.className = 'btn btn-modal-next add-btn';
            nextBtn.onclick = () => {
                const options = Object.values(_state.selectedOptions).map(o => ({
                    groupLabel: o.groupLabel,
                    optionLabel: o.optionLabel,
                    price: o.price || 0,
                }));
                const optionSummary = options.map(o => o.groupLabel + ': ' + o.optionLabel).join(', ');
                _state.onComplete({
                    serviceId: _state.service.id,
                    serviceName: _state.service.label,
                    variantId: _state.selectedVariant.id,
                    variantLabel: _state.selectedVariant.label,
                    options,
                    optionSummary,
                    unitPrice: _getUnitPrice(),
                    qty: _state.qty,
                });
                _state.modal.hide();
            };
        }

        _updateItemTotal();
    }

    return { open };
})();

</script>
