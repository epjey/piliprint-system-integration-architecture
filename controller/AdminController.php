// ============================================================
// CONTROLLER: AdminController.js (served as .php)
// Handles logic for the admin dashboard side
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
initAdmin();
});

function initAdmin() {
// Nav Click Handlers
const navItems = document.querySelectorAll('.admin-nav-item');
navItems.forEach(item => {
item.addEventListener('click', (e) => {
// Remove active class from all
navItems.forEach(n => n.classList.remove('active'));
// Add active class to clicked
const currentItem = e.currentTarget;
currentItem.classList.add('active');

const target = currentItem.getAttribute('data-target');
loadAdminTab(target);
});
});

// Top Right Buttons
document.getElementById('btnGoToPOS').addEventListener('click', () => {
window.location.href = 'index.php';
});

document.getElementById('btnLogout').addEventListener('click', () => {
Swal.fire({
title: 'Logout?',
text: 'Are you sure you want to logout of PrintMaster Admin?',
icon: 'question',
showCancelButton: true,
confirmButtonColor: '#2e4a6e',
cancelButtonColor: '#d33',
confirmButtonText: 'Yes, logout'
}).then((result) => {
if (result.isConfirmed) {
Swal.fire({
icon: 'success',
title: 'Logged Out',
text: 'Redirecting to login page...',
showConfirmButton: false,
timer: 1500
}).then(() => {
window.location.href = 'view/auth/login.php';
});
}
});
});

// Load initial tab (dashboard)
loadAdminTab('dashboard');

// Start Navbar Datetime ticker
startAdminDatetimeTicker();
}

function loadAdminTab(target) {
const headerEl = document.getElementById('adminContentHeader');
const bodyEl = document.getElementById('adminContentBody');

// Capitalize header title
headerEl.textContent = target.charAt(0).toUpperCase() + target.slice(1);

// Call view rendering based on target
if (target === 'dashboard') {
AdminView.renderDashboard(bodyEl);
} else if (target === 'services') {
AdminView.renderServices(bodyEl);
} else if (target === 'transactions') {
AdminView.renderTransactions(bodyEl);
} else if (target === 'users') {
AdminView.renderUsers(bodyEl);
} else if (target === 'settings') {
AdminView.renderSettings(bodyEl);
}
}

// Datetime ticker in navbar
function startAdminDatetimeTicker() {
const el = document.getElementById('navDatetime');
if (!el) return;

function update() {
const now = new Date();
const optionsDate = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
const dateStr = now.toLocaleDateString('en-US', optionsDate);
const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
});
el.textContent = `${dateStr} | ${timeStr}`;
}

update();
setInterval(update, 1000);
}