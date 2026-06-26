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
    let orderCounter = 73; // starts at 73 so first order is #0074

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

    function placeOrder(paymentInfo) {
        orderCounter++;
        const orderNum = String(orderCounter).padStart(4, '0');
        const now = new Date();
        const txn = {
            orderNum,
            items: [...currentOrder],
            total: getTotal(),
            customer: paymentInfo.customer,
            contact: paymentInfo.contact,
            paymentMethod: paymentInfo.paymentMethod,
            amountPaid: paymentInfo.amountPaid,
            change: paymentInfo.amountPaid - getTotal(),
            date: now.toISOString().slice(0, 10),
            time: now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true }),
        };
        
        clearOrder();
        return txn;
    }

    function getTransactions() {
        // Will be retrieved from database later
        return [];
    }

    function getTransactionByNum(num) {
        // Will be retrieved from database later
        return null;
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
        getTransactions,
        getTransactionByNum,
    };
})();
