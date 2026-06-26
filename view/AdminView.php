// ============================================================
// VIEW: AdminView.js (served as .php)
// Handles DOM updates for the admin dashboard
// ============================================================

const AdminView = (() => {

return {
renderDashboard: (container) => {
// Get stats from OrderModel (as an example)
const txns = typeof OrderModel !== 'undefined' ? OrderModel.getTransactions() : [];
const totalRevenue = txns.reduce((sum, t) => sum + t.total, 0);

container.innerHTML = `
<div class="stat-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total Transactions</span>
            <span class="stat-value">${txns.length}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#DCFCE7;color:#15803D;">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Revenue Today</span>
            <span class="stat-value">₱${totalRevenue.toFixed(2)}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#B45309;">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Active Users</span>
            <span class="stat-value">4</span>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-title">Recent Activity</div>
    <div style="color:#64748B;font-size:0.9rem;">
        No recent activity recorded today.
    </div>
</div>
`;
},

renderServices: (container) => {
container.innerHTML = `
<div class="admin-card">
    <div class="admin-card-title d-flex justify-content-between align-items-center">
        <span>Manage Services</span>
        <button class="btn btn-sm btn-primary" style="background:#0F172A;border-color:#0F172A;">+ Add Service</button>
    </div>
    <p style="color:#64748B;font-size:0.9rem;">This module will allow the admin to add, edit, or remove printing
        services and variants.</p>
</div>
`;
},

renderTransactions: (container) => {
container.innerHTML = `
<div class="admin-card">
    <div class="admin-card-title d-flex justify-content-between align-items-center">
        <span>Transaction History</span>
        <button class="btn btn-sm btn-success" style="background:#10B981;border-color:#10B981;">Export CSV</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered w-100">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center" style="color:#64748B;">Use the Cashier POS to generate test
                        transactions.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
`;
},

renderUsers: (container) => {
container.innerHTML = `
<div class="admin-card">
    <div class="admin-card-title d-flex justify-content-between align-items-center">
        <span>System Users</span>
        <button class="btn btn-sm btn-primary" style="background:#0F172A;border-color:#0F172A;">+ Add User</button>
    </div>
    <p style="color:#64748B;font-size:0.9rem;">Manage cashier and admin accounts here.</p>
</div>
`;
},

renderSettings: (container) => {
container.innerHTML = `
<div class="admin-card">
    <div class="admin-card-title">System Settings</div>
    <p style="color:#64748B;font-size:0.9rem;">Configure store details, tax rates, and receipt footer information.</p>
</div>
`;
}
};
})();