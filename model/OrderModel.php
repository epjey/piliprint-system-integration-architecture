<script>
// ============================================================
//  OrderModel.php
//  JavaScript served as a PHP file.
//  When using a PHP server, add header('Content-Type: application/javascript') above.
// ============================================================
// ============================================================
//  MODEL: OrderModel.js
//  Manages the current order and transaction history
// ============================================================

const OrderModel = (() => {

    let currentOrder = [];
    let transactions = [];

    // ---- Current Order ----

    function addItem(item) {
        // item: { serviceId, serviceName, variantId, variantLabel, options:[{groupLabel, optionLabel, price}], basePrice, qty }
        currentOrder.push({ ...item, _id: Date.now() + Math.random() });
    }

    function removeItem(_id) {
        currentOrder = currentOrder.filter(i => i._id !== _id);
    }

    function clearOrder() {
        currentOrder = [];
    }

    function getItems() {
        return [...currentOrder];
    }

    function getTotal() {
        return currentOrder.reduce((sum, item) => sum + (item.unitPrice * item.qty), 0);
    }

    function isEmpty() {
        return currentOrder.length === 0;
    }

    function updateQty(_id, qty) {
        const item = currentOrder.find(i => i._id === _id);
        if (item) {
            item.qty = qty;
        }
    }

    // ---- Transactions ----

    async function placeOrder(paymentInfo) {
        const payload = {
            items: [...currentOrder],
            total: getTotal(),
            customer: paymentInfo.customer,
            contact: paymentInfo.contact,
            paymentMethod: paymentInfo.paymentMethod,
            amountPaid: paymentInfo.amountPaid,
            change: paymentInfo.amountPaid - getTotal()
        };
        
        try {
            const res = await fetch('api/transactions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.success) {
                clearOrder();
                // refresh local list
                await loadTransactions();
                return data.transaction;
            } else {
                console.error("Failed to place order:", data.message);
                return null;
            }
        } catch (e) {
            console.error(e);
            return null;
        }
    }

    async function loadTransactions() {
        try {
            const res = await fetch('api/transactions.php');
            const data = await res.json();
            if (data.success) {
                transactions = data.transactions;
            }
        } catch (e) {
            console.error(e);
        }
    }

    function getTransactions() {
        return transactions;
    }

    function getTransactionByNum(num) {
        return transactions.find(t => t.orderNum === num) || null;
    }

    return {
        addItem,
        removeItem,
        clearOrder,
        getItems,
        getTotal,
        isEmpty,
        updateQty,
        placeOrder,
        loadTransactions,
        getTransactions,
        getTransactionByNum,
    };
})();

</script>
