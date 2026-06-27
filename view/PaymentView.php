<script>
// ============================================================
//  PaymentView.php
//  JavaScript served as a PHP file.
//  When using a PHP server, add header('Content-Type: application/javascript') above.
// ============================================================
// ============================================================
//  VIEW: PaymentView.js
//  Payment Calculator modal – numpad, change calc, confirm
// ============================================================

const PaymentView = (() => {

    let _total = 0;
    let _numStr = '';
    let _onConfirm = null;
    let _modal = null;

    function open(total, onConfirm) {
        _total = total;
        _numStr = '';
        _onConfirm = onConfirm;

        document.getElementById('paymentTotalAmt').textContent = 'P ' + total.toFixed(2);
        
        const display = document.getElementById('numpadDisplay');
        display.value = '';
        
        document.getElementById('paymentChangeDisplay').textContent = 'Change: P 0.00';
        document.getElementById('paymentChangeDisplay').style.color = '#c0392b';
        document.getElementById('customerName').value = '';
        document.getElementById('contactNo').value = '';

        // Reset payment method selection to Cash on load
        const paymentMethodSelect = document.getElementById('paymentMethod');
        paymentMethodSelect.value = 'Cash';

        _modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        _modal.show();

        // Toggle visibility between Numpad and GCash QR
        const togglePaymentMethodView = () => {
            const method = paymentMethodSelect.value;
            const qrContainer = document.getElementById('gcashQrContainer');
            const numpadContainer = document.getElementById('numpadContainer');
            const qrAmount = document.getElementById('gcashQrAmount');

            if (method === 'GCash') {
                qrContainer.style.display = 'block';
                numpadContainer.style.display = 'none';
                qrAmount.textContent = 'P ' + _total.toFixed(2);
                _numStr = _total.toFixed(2);
            } else {
                qrContainer.style.display = 'none';
                numpadContainer.style.display = 'block';
                _numStr = '';
            }
            display.value = _numStr;
            _updateChange();
        };

        paymentMethodSelect.onchange = togglePaymentMethodView;
        togglePaymentMethodView();

        // Attach numpad
        document.querySelectorAll('.numpad-btn').forEach(btn => {
            btn.onclick = null;
            btn.addEventListener('click', _handleNumpad);
        });

        // Keyboard input handler
        display.oninput = null;
        display.addEventListener('input', (e) => {
            let val = e.target.value.replace(/[^0-9.]/g, '');
            const parts = val.split('.');
            if (parts.length > 2) {
                val = parts[0] + '.' + parts.slice(1).join('');
            }
            _numStr = val;
            display.value = val;
            _updateChange();
        });

        document.getElementById('btnConfirmPayment').onclick = _confirm;

        // Auto-capitalize first letter and restrict to letters only (no spaces, numbers, special chars)
        document.getElementById('customerName').oninput = function() {
            let v = this.value.replace(/[^a-zA-Z]/g, '');
            if (v.length > 0) {
                this.value = v.charAt(0).toUpperCase() + v.slice(1);
            } else {
                this.value = '';
            }
        };

        // Numbers only, max 11 digits for contact
        document.getElementById('contactNo').oninput = function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
        };
    }

    function _updateChange() {
        const paid = parseFloat(_numStr) || 0;
        const change = paid - _total;
        const el = document.getElementById('paymentChangeDisplay');
        el.textContent = 'Change: P ' + (change >= 0 ? change.toFixed(2) : '0.00');
        el.style.color = change >= 0 ? '#28a745' : '#c0392b';
    }

    function _handleNumpad(e) {
        const val = e.currentTarget.dataset.val;
        if (val === 'C') {
            _numStr = '';
        } else if (val === 'Bksp') {
            _numStr = _numStr.slice(0, -1);
        } else {
            if (_numStr.length < 10) _numStr += val;
        }
        document.getElementById('numpadDisplay').value = _numStr;
        _updateChange();
    }


    function _confirm() {
        const paid = parseFloat(_numStr) || 0;
        const name = document.getElementById('customerName').value.trim();
        const contact = document.getElementById('contactNo').value.trim();
        const method = document.getElementById('paymentMethod').value;

        if (paid < _total) {
            Swal.fire({ icon: 'warning', title: 'Insufficient Payment', text: `Amount paid (₱${paid.toFixed(2)}) is less than total (₱${_total.toFixed(2)}).`, confirmButtonColor: '#2e4a6e' });
            return;
        }
        const change = paid - _total;

        Swal.fire({
            icon: 'info',
            title: 'Payment Successful!',
            html: `Change: P ${change.toFixed(2)}`,
            confirmButtonText: 'OK',
            confirmButtonColor: '#2e4a6e',
        }).then(() => {
            _modal.hide();
            _onConfirm && _onConfirm({ customer: name, contact, paymentMethod: method, amountPaid: paid, change });
        });
    }

    return { open };
})();

</script>
