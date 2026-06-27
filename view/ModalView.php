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
                    if (_state.selectedVariant && _state.selectedVariant.id === v.id) {
                        // deselect
                        _state.selectedVariant = null;
                        _state.selectedOptions = {};
                        card.classList.remove('selected');
                    } else {
                        _state.selectedVariant = v;
                        _state.selectedOptions = {};
                        document.querySelectorAll('.variant-card').forEach(c => c.classList.remove('selected'));
                        card.classList.add('selected');
                    }
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


            body.querySelectorAll('.option-card').forEach(card => {
                card.addEventListener('click', () => {
                    const gid = card.dataset.gid;
                    const alreadySelected = _state.selectedOptions[gid] && _state.selectedOptions[gid].optionId === card.dataset.optid;
                    if (alreadySelected) {
                        // deselect
                        card.classList.remove('selected');
                        delete _state.selectedOptions[gid];
                    } else {
                        body.querySelectorAll(`[data-gid="${gid}"]`).forEach(c => c.classList.remove('selected'));
                        card.classList.add('selected');
                        _state.selectedOptions[gid] = {
                            optionId: card.dataset.optid,
                            optionLabel: card.dataset.olabel,
                            price: parseFloat(card.dataset.price) || 0,
                            groupLabel: card.dataset.glabel,
                        };
                    }
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
          <div class="qty-summary-title">${_state.service.label} — ${_state.selectedVariant ? _state.selectedVariant.label : 'No variant selected'}</div>
          <div class="qty-summary-sub">Base ₱${_state.selectedVariant ? _state.selectedVariant.price.toFixed(2) : '0.00'} · ${optKeys || 'No options'}</div>
        </div>
        <div class="qty-counter">
          <button class="qty-btn minus" id="qtyMinus">−</button>
          <input type="text" inputmode="numeric" class="qty-display" id="qtyInput" maxlength="3" value="${_state.qty}" autocomplete="off" />
          <button class="qty-btn plus" id="qtyPlus">+</button>
        </div>
        <div class="qty-unit">copies / sets</div>
        <div id="qtyError" style="color:#EF4444;font-size:0.8rem;text-align:center;margin-top:6px;min-height:18px;"></div>
      `;

            const qtyInput  = document.getElementById('qtyInput');
            const qtyError  = document.getElementById('qtyError');
            const QTY_MAX   = 100;

            function showQtyError(msg) {
                qtyError.textContent = msg;
            }
            function clearQtyError() {
                qtyError.textContent = '';
            }

            // Returns parsed integer or null + sets error message
            function parseAndValidate(raw) {
                const trimmed = String(raw).trim();
                if (trimmed === '') { showQtyError('Quantity is required.'); return null; }
                if (!/^\d+$/.test(trimmed)) { showQtyError('Quantity must be a whole number — no letters, decimals, or special characters.'); return null; }
                const n = parseInt(trimmed, 10);
                if (n === 0)         { showQtyError('Quantity cannot be zero.'); return null; }
                if (n > QTY_MAX)     { showQtyError(`Quantity cannot exceed ${QTY_MAX}.`); return null; }
                clearQtyError();
                return n;
            }

            // Block keys that can never form a valid integer
            qtyInput.addEventListener('keydown', (e) => {
                const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
                if (allowed.includes(e.key)) return;
                if (!/^\d$/.test(e.key)) { e.preventDefault(); }
            });

            // Live strip: remove anything that isn't a digit
            qtyInput.addEventListener('input', () => {
                const stripped = qtyInput.value.replace(/[^0-9]/g, '');
                if (qtyInput.value !== stripped) qtyInput.value = stripped;
                const n = parseAndValidate(qtyInput.value);
                if (n !== null) { _state.qty = n; _updateItemTotal(); }
            });

            // On blur: normalise leading zeros, clamp, commit
            qtyInput.addEventListener('blur', () => {
                const n = parseAndValidate(qtyInput.value);
                if (n === null) {
                    qtyInput.value = _state.qty; // restore last valid
                    clearQtyError();
                } else {
                    const clamped = Math.min(n, QTY_MAX);
                    qtyInput.value = clamped;
                    _state.qty = clamped;
                    _updateItemTotal();
                }
            });

            document.getElementById('qtyMinus').addEventListener('click', () => {
                let val = parseInt(qtyInput.value) || 1;
                if (val > 1) {
                    val--;
                    qtyInput.value = val;
                    _state.qty = val;
                    clearQtyError();
                    _updateItemTotal();
                }
            });

            document.getElementById('qtyPlus').addEventListener('click', () => {
                const QTY_MAX = 100;
                let val = parseInt(qtyInput.value) || 0;
                if (val >= QTY_MAX) {
                    showQtyError(`Quantity cannot exceed ${QTY_MAX}.`);
                    return;
                }
                val++;
                qtyInput.value = val;
                _state.qty = val;
                clearQtyError();
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
                const n = parseAndValidate(qtyInput.value);
                if (n === null) {
                    Swal.fire({ icon: 'warning', title: 'Invalid Quantity', text: qtyError.textContent, confirmButtonColor: '#2e4a6e' });
                    return;
                }
                if (!_state.selectedVariant) {
                    Swal.fire({ icon: 'warning', title: 'No Variant Selected', text: 'Please go back and select a variant.', confirmButtonColor: '#2e4a6e' });
                    return;
                }
                _state.qty = n;
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
