<script>
// ============================================================
// VIEW: AdminView.js (served as .php)
// Handles DOM updates for the admin dashboard
// ============================================================

const AdminView = (() => {

return {
renderDashboard: (container) => {
// Get stats from OrderModel
const txns = typeof OrderModel !== 'undefined' ? OrderModel.getTransactions() : [];
const totalRevenue = txns.reduce((sum, t) => sum + (t.total || 0), 0);

// Build monthly sales from real transaction data (last 6 months)
const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const now = new Date();
const months = [];
const monthlyData = [];
for (let i = 5; i >= 0; i--) {
const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
months.push(monthNames[d.getMonth()]);
const monthTotal = txns
.filter(t => {
const td = new Date(t.date || t.createdAt || null);
return td.getFullYear() === d.getFullYear() && td.getMonth() === d.getMonth();
})
.reduce((sum, t) => sum + (t.total || 0), 0);
monthlyData.push(monthTotal);
}

// Build service data from today's transactions
const todayStr = new Date().toDateString();
const svcCount = {};
txns.forEach(t => {
const td = new Date(t.date || t.createdAt || null);
if (td.toDateString() !== todayStr) return;
(t.items || []).forEach(item => {
    const sName = item.serviceName || item.name || item.service || 'Unknown';
    svcCount[sName] = (svcCount[sName] || 0) + (item.qty || 1);
});
});
const serviceLabels = Object.keys(svcCount).length ? Object.keys(svcCount) : ['No data'];
const serviceData = Object.keys(svcCount).length ? Object.values(svcCount) : [0];

// % change vs previous month
const curMonth = monthlyData[monthlyData.length - 1];
const prevMonth = monthlyData[monthlyData.length - 2] || 0;
const pctChange = prevMonth > 0 ? (((curMonth - prevMonth) / prevMonth) * 100).toFixed(0) : (curMonth > 0 ? 100 : 0);
const nowStr = new Date().toLocaleString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true, month: '2-digit',
day: '2-digit', year: 'numeric' });

container.innerHTML = `
<div style="padding:0;">
    <!-- Welcome Header -->
    <div style="margin-bottom:18px;">
        <h2 style="font-size:1.6rem;font-weight:700;color:#0F172A;margin:0 0 2px;">Welcome back, Admin</h2>
        <p style="color:#64748B;font-size:0.88rem;margin:0;">Here's what's happening today.</p>
    </div>

    <!-- Two-Panel Charts Row -->
    <div style="display:flex;gap:20px;height:calc(100vh - 220px);">

        <!-- LEFT: Monthly Sales Summary -->
        <div class="admin-card" style="flex:1;min-width:0;display:flex;flex-direction:column;">
            <div style="font-weight:700;font-size:0.95rem;color:#0F172A;margin-bottom:15px;">Monthly Sales Summary</div>
            <div style="position:relative;flex:1;min-height:0;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
            <!-- Summary footer -->
            <div style="display:flex;gap:0;border-top:1px solid #E2E8F0;margin-top:15px;">
                <div style="flex:1;padding:15px 14px 5px;border-right:1px solid #E2E8F0;">
                    <div style="font-size:1.1rem;font-weight:700;color:#2563EB;">₱${totalRevenue.toFixed(2)}</div>
                    <div style="font-size:0.75rem;color:#64748B;margin-top:2px;">Total Due</div>
                </div>
                <div style="flex:1;padding:15px 14px 5px;border-right:1px solid #E2E8F0;">
                    <div style="font-size:1.1rem;font-weight:700;color:#16A34A;">${pctChange > 0 ? '+' : ''}${pctChange}%
                    </div>
                    <div style="font-size:0.75rem;color:#64748B;margin-top:2px;">vs Last Month</div>
                </div>
                <div style="flex:1;padding:15px 14px 5px;">
                    <div style="font-size:1.1rem;font-weight:700;color:#0F172A;">${txns.length}</div>
                    <div style="font-size:0.75rem;color:#64748B;margin-top:2px;">Transactions</div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Most Service Offered Today -->
        <div class="admin-card" style="flex:1;min-width:0;display:flex;flex-direction:column;">
            <div style="font-weight:700;font-size:0.95rem;color:#0F172A;margin-bottom:15px;">Most Service Offered Today
            </div>
            <div style="position:relative;flex:1;min-height:0;">
                <canvas id="serviceChart"></canvas>
            </div>
            <!-- Empty footer layout to match the left side's height -->
            <div style="display:flex;justify-content:flex-end;border-top:1px solid #E2E8F0;margin-top:15px;">
                <div style="padding:15px 14px 5px;font-size:0.75rem;color:#64748B;">
                    Updated today at ${nowStr}
                </div>
            </div>
        </div>

    </div>
</div>
`;

// ---- Monthly Sales Bar Chart ----
const ctx1 = document.getElementById('monthlySalesChart').getContext('2d');
new Chart(ctx1, {
type: 'bar',
data: {
labels: months,
datasets: [{
label: 'Amount (₱)',
data: monthlyData,
backgroundColor: '#4A7FB5',
borderRadius: 4,
borderSkipped: false,
}]
},
options: {
responsive: true,
maintainAspectRatio: false,
plugins: { legend: { display: false } },
scales: {
x: {
title: { display: true, text: 'Month', font: { size: 11 }, color: '#64748B' },
grid: { display: false },
ticks: { color: '#64748B', font: { size: 11 } }
},
y: {
title: { display: true, text: 'Amount (₱)', font: { size: 11 }, color: '#64748B' },
beginAtZero: true,
ticks: { color: '#64748B', font: { size: 11 } },
grid: { color: '#F1F5F9' }
}
}
}
});

// ---- Service Horizontal Bar Chart ----
const ctx2 = document.getElementById('serviceChart').getContext('2d');
const hasServiceData = serviceData.some(v => v > 0);
new Chart(ctx2, {
type: 'bar',
data: {
labels: serviceLabels,
datasets: [{
label: 'Orders',
data: hasServiceData ? serviceData : [0, 0, 0, 0, 0],
backgroundColor: '#4A7FB5',
borderRadius: 4,
}]
},
options: {
indexAxis: 'y',
responsive: true,
maintainAspectRatio: false,
plugins: {
legend: { display: false },
...(hasServiceData ? {} : {
beforeDraw(chart) {
const { ctx, width, height } = chart;
ctx.save();
ctx.textAlign = 'center';
ctx.textBaseline = 'middle';
ctx.fillStyle = '#94A3B8';
ctx.font = '13px Segoe UI';
ctx.fillText('No Sales Today', width / 2, height / 2);
ctx.restore();
}
})
},
scales: {
x: {
title: { display: true, text: 'Orders', font: { size: 11 }, color: '#64748B' },
beginAtZero: true,
ticks: { color: '#64748B', font: { size: 11 } },
grid: { color: '#F1F5F9' }
},
y: {
title: { display: true, text: 'Service', font: { size: 11 }, color: '#64748B' },
ticks: { color: '#64748B', font: { size: 11 } },
grid: { display: false }
}
}
}
});
},

renderServices: (container, services = [], selectedServiceId = null, currentTab = 'variants') => {
    const activeServices = services.filter(s => !s.isArchived);
    if (!selectedServiceId && activeServices.length > 0) selectedServiceId = activeServices[0].id;
    const selectedService = services.find(s => s.id == selectedServiceId) || null;

    const serviceListHtml = activeServices.length > 0
        ? activeServices.map(s => `
            <div class="service-list-item ${s.id == selectedServiceId ? 'active' : ''}" data-id="${s.id}" style="display:flex;align-items:center;padding:12px 20px;border-left:3px solid ${s.id == selectedServiceId ? '#0F172A' : 'transparent'};background:${s.id == selectedServiceId ? '#F1F5F9' : 'transparent'};border-bottom:1px solid #F1F5F9;cursor:pointer;">
                <div style="width:36px;height:36px;background:#fff;border-radius:6px;border:1px solid #E2E8F0;display:flex;align-items:center;justify-content:center;margin-right:12px;">
                    ${((s.icon || '📦').startsWith('data:image') || (s.icon || '').startsWith('assets/')) ? '<img src="' + s.icon + '" style="width:28px;height:28px;border-radius:4px;object-fit:cover;" />' : (s.icon || '📦').includes('<svg') ? '<div style="width:28px;height:28px;">' + s.icon + '</div>' : '<span style="font-size:1.2rem;">' + (s.icon || '📦') + '</span>'}
                </div>
                <div style="flex:1;">
                    <div style="font-size:0.9rem;font-weight:600;color:#0F172A;">${s.name}</div>
                    <div style="font-size:0.75rem;color:#94A3B8;">${s.variantsCount || 0}v · ${s.optionsCount || 0}opt</div>
                </div>
            </div>
        `).join('')
        : `<div style="padding: 20px; color: #94A3B8; text-align: center; font-size: 0.85rem;">No active services found.</div>`;

    let rightContentHtml = '';

    if (selectedService) {
        let tabBodyHtml = '';
        let tabActionText = '';

        if (currentTab === 'variants') {
            tabActionText = 'Add Variant';
            tabBodyHtml = (selectedService.variants && selectedService.variants.length > 0)
                ? selectedService.variants.map((v, idx) => `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;background:${idx % 2 === 0 ? '#FDFDFD' : '#fff'};border-bottom:1px solid #E2E8F0;">
                        <div>
                            <div style="font-size:0.9rem;font-weight:600;color:#0F172A;">${v.name}</div>
                            <div style="font-size:0.8rem;color:#94A3B8;margin-top:2px;">Base price: ₱${parseFloat(v.basePrice || 0).toFixed(2)}</div>
                        </div>
                        <div style="display:flex;gap:15px;">
                            <button class="btn-remove-item" data-id="${v.id}" data-type="variant" style="background:none;border:none;color:#EF4444;font-size:0.85rem;font-weight:600;cursor:pointer;">Remove</button>
                        </div>
                    </div>`).join('')
                : `<div style="padding: 20px; color: #94A3B8; text-align: center; font-size: 0.85rem;">No variants found.</div>`;
        } else if (currentTab === 'options') {
            tabActionText = 'Add Option';
            tabBodyHtml = (selectedService.optionGroups && selectedService.optionGroups.length > 0)
                ? selectedService.optionGroups.map((g) => {
                    const badgeColor = g.type === 'Page Tier' ? 'background:#FEF3C7;color:#D97706;' : 'background:#DBEAFE;color:#1D4ED8;';

                    const optionsHtml = (g.options && g.options.length > 0)
                        ? g.options.map(o => `
                            <div class="option-item-box" data-group-id="${g.id}" data-option-id="${o.id}" style="background:#fff;border:1px solid #CBD5E1;border-radius:6px;padding:8px 16px;font-size:0.85rem;color:#0F172A;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 1px 2px rgba(0,0,0,0.02);position:relative;">
                                <span>${o.name}</span>
                                ${parseFloat(o.price || 0) > 0 ? `<span style="color:#64748B;font-size:0.8rem;">(+₱${parseFloat(o.price).toFixed(1)})</span>` : ''}
                                <span class="delete-option-item-x" style="color:#EF4444;font-weight:bold;margin-left:4px;">&times;</span>
                            </div>
                        `).join('')
                        : '';

                    return `
                        <div class="option-group-card" style="background:#fff;border:1px solid #E2E8F0;border-radius:8px;padding:20px;margin-bottom:15px;position:relative;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:15px;">
                                <div style="display:flex;align-items:center;">
                                    <span style="font-weight:700;font-size:0.95rem;color:#0F172A;">${g.name}</span>
                                    <span style="${badgeColor}padding:2px 8px;border-radius:4px;font-size:0.7rem;font-weight:600;margin-left:10px;text-transform:uppercase;">${g.type || 'Choice'}</span>
                                </div>
                                <button class="btn-remove-group" data-id="${g.id}" style="background:none;border:none;color:#EF4444;font-size:0.85rem;font-weight:600;cursor:pointer;">Remove</button>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                                ${optionsHtml}
                                <button class="btn-add-option-item" data-group-id="${g.id}" style="background:none;border:1px dashed #CBD5E1;border-radius:6px;padding:8px 16px;font-size:0.85rem;color:#F97316;font-weight:600;cursor:pointer;">+ Add Item</button>
                            </div>
                        </div>
                    `;
                }).join('')
                : `<div style="padding: 20px; color: #94A3B8; text-align: center; font-size: 0.85rem;">No options found.</div>`;
        } else if (currentTab === 'pricing') {
            const hasVariants = selectedService.variants && selectedService.variants.length > 0;

            if (!hasVariants) {
                tabBodyHtml = `<div style="padding: 30px; text-align: center; color: #64748B; font-size: 0.95rem;">
                    Please add at least one variant to preview pricing.
                </div>`;
            } else {
                if (!window.pricingSimulationState || window.pricingSimulationState.serviceId !== selectedService.id) {
                    window.pricingSimulationState = {
                        serviceId: selectedService.id,
                        selectedVariantId: null,
                        selectedOptionIds: {}
                    };
                }

                const variantButtons = selectedService.variants.map(v => {
                    const isActive = window.pricingSimulationState.selectedVariantId == v.id;
                    const activeStyle = isActive ? 'background:#4A7FB5;color:#fff;border-color:#4A7FB5;' : 'background:#fff;color:#0F172A;border-color:#CBD5E1;';
                    return `<button class="sim-btn-variant" data-id="${v.id}" style="padding:10px 20px;font-size:0.9rem;font-weight:600;border-radius:6px;border:1px solid;${activeStyle}cursor:pointer;min-width:120px;text-align:center;transition:all 0.2s;">
                        ${v.name} (₱${parseFloat(v.basePrice || 0).toFixed(1)})
                    </button>`;
                }).join('');

                let optionGroupsHtml = '';
                if (selectedService.optionGroups && selectedService.optionGroups.length > 0) {
                    optionGroupsHtml = selectedService.optionGroups.map(g => {
                        const optionButtons = g.options.map(o => {
                            const isActive = window.pricingSimulationState.selectedOptionIds[g.id] == o.id;
                            const activeStyle = isActive ? 'background:#D97706;color:#fff;border-color:#D97706;' : 'background:#fff;color:#0F172A;border-color:#CBD5E1;';
                            const priceText = parseFloat(o.price || 0) > 0 ? ` (+₱${parseFloat(o.price).toFixed(1)})` : '';
                            return `<button class="sim-btn-option" data-group-id="${g.id}" data-option-id="${o.id}" style="padding:10px 20px;font-size:0.9rem;font-weight:600;border-radius:6px;border:1px solid;${activeStyle}cursor:pointer;min-width:120px;text-align:center;transition:all 0.2s;">
                                ${o.name}${priceText}
                            </button>`;
                        }).join('');

                        return `
                            <div style="margin-top:20px;">
                                <div style="font-size:0.75rem;font-weight:700;color:#64748B;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:10px;">${g.name}</div>
                                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                                    ${optionButtons}
                                </div>
                            </div>
                        `;
                    }).join('');
                }

                tabBodyHtml = `
                    <div style="background:#F5F7FB;border:1px solid #E2E8F0;border-radius:8px;padding:25px;display:flex;flex-direction:column;gap:10px;min-height:300px;justify-content:space-between;">
                        <div>
                            <div style="font-size:0.75rem;font-weight:700;color:#64748B;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:10px;">Select Variant</div>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                                ${variantButtons}
                            </div>

                            ${optionGroupsHtml}
                        </div>

                        <!-- Estimated Total footer -->
                        <div style="border-top:1px solid #E2E8F0;padding-top:20px;margin-top:20px;display:flex;align-items:flex-end;justify-content:space-between;">
                            <div>
                                <div style="font-size:0.9rem;font-weight:700;color:#0F172A;">Estimated Total</div>
                                <div id="pricingSimBreakdown" style="font-size:0.8rem;color:#94A3B8;margin-top:5px;font-style:italic;">Calculated price details</div>
                            </div>
                            <div id="pricingSimTotal" style="font-size:2rem;font-weight:700;color:#2563EB;">₱0.00</div>
                        </div>
                    </div>
                `;
            }
        }

        const tabStyles = (isActive) => isActive
            ? 'background:#fff;color:#0F172A;font-weight:600;font-size:0.85rem;padding:10px 25px;border:1px solid #E2E8F0;border-bottom:none;border-radius:6px 6px 0 0;position:relative;top:1px;cursor:pointer;'
            : 'color:#64748B;font-weight:600;font-size:0.85rem;padding:10px 25px;cursor:pointer;';

        rightContentHtml = `
            <!-- Header Card -->
            <div style="background:#fff;border:1px solid #E2E8F0;border-radius:8px;padding:20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:25px;">
                    <div id="btnEditServiceIcon" title="Click to change icon" style="width:75px;height:75px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;display:flex;align-items:center;justify-content:center;position:relative;cursor:pointer;transition:all 0.2s;">
                        ${((selectedService.icon || '📦').startsWith('data:image') || (selectedService.icon || '').startsWith('assets/')) ? '<img src="' + selectedService.icon + '" style="width:55px;height:55px;border-radius:6px;object-fit:cover;" />' : (selectedService.icon || '📦').includes('<svg') ? '<div style="width:55px;height:55px;">' + selectedService.icon + '</div>' : '<span style="font-size:2.2rem;">' + (selectedService.icon || '📦') + '</span>'}
                        <div style="position:absolute;bottom:-5px;right:-5px;background:#4A7FB5;color:#fff;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,0.2);">
                            ✎
                        </div>
                    </div>
                    <div style="font-size:1.4rem;font-weight:700;color:#0F172A;">${selectedService.name}</div>
                </div>
                <div style="display:flex;gap:15px;align-items:center;">
                    <div style="border:1px solid #E2E8F0;padding:12px 25px;border-radius:6px;text-align:center;min-width:90px;">
                        <div style="font-size:1.4rem;font-weight:700;color:#4A7FB5;">${selectedService.variantsCount || 0}</div>
                        <div style="font-size:0.7rem;color:#94A3B8;text-transform:uppercase;margin-top:2px;">Variants</div>
                    </div>
                    <div style="border:1px solid #E2E8F0;padding:12px 25px;border-radius:6px;text-align:center;min-width:90px;">
                        <div style="font-size:1.4rem;font-weight:700;color:#4A7FB5;">${selectedService.optionsCount || 0}</div>
                        <div style="font-size:0.7rem;color:#94A3B8;text-transform:uppercase;margin-top:2px;">Options</div>
                    </div>
                    ${!selectedService.isArchived ? `
                        <button id="btnArchiveService" class="btn btn-sm" style="background:#FEF2F2;color:#EF4444;border:1px solid #FCA5A5;font-weight:600;height:40px;padding:0 15px;white-space:nowrap;">
                            Archive
                        </button>
                    ` : `
                        <span style="background:#F1F5F9;color:#64748B;padding:8px 15px;border-radius:6px;font-size:0.8rem;font-weight:600;border:1px solid #E2E8F0;">
                            Archived
                        </span>
                    `}
                </div>
            </div>

            <!-- Tab Content -->
            <div style="background:#fff;border:1px solid #E2E8F0;border-radius:8px;flex:1;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                <div style="display:flex;background:#F8FAFC;border-bottom:1px solid #E2E8F0;padding-top:10px;padding-left:15px;gap:5px;">
                    <div class="admin-service-tab" data-tab="variants" style="${tabStyles(currentTab === 'variants')}">Variants</div>
                    <div class="admin-service-tab" data-tab="options" style="${tabStyles(currentTab === 'options')}">Options</div>
                    <div class="admin-service-tab" data-tab="pricing" style="${tabStyles(currentTab === 'pricing')}">Pricing Preview</div>
                </div>
                <div style="padding:25px;overflow-y:auto;flex:1;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                        <span style="font-size:0.85rem;color:#64748B;">${currentTab === 'pricing' ? 'Simulate how the cashier will see this service' : currentTab === 'options' ? 'Configure add-on options & pricing tiers' : 'Define the ' + currentTab + ' for ' + selectedService.name}</span>
                        ${currentTab === 'variants' ? '<button id="btnAddVariant" class="btn btn-sm" style="background:#F97316;color:#fff;border:none;font-weight:600;padding:5px 15px;">+ Add Variant</button>' : ''}
                        ${currentTab === 'options' ? '<button id="btnAddVariant" class="btn btn-sm" style="background:#F97316;color:#fff;border:none;font-weight:600;padding:5px 15px;">+ Add Option</button>' : ''}
                    </div>
                    ${currentTab === 'options' || currentTab === 'pricing' ? tabBodyHtml : '<div style="display:flex;flex-direction:column;border:1px solid #E2E8F0;border-radius:6px;overflow:hidden;">' + tabBodyHtml + '</div>'}
                </div>
            </div>
        `;
    } else {
        rightContentHtml = `<div style="flex:1;display:flex;align-items:center;justify-content:center;color:#94A3B8;">Select a service from the left to view details.</div>`;
    }

    container.innerHTML = `
    <div style="display:flex;height:calc(100vh - 66px);margin:-25px;background:#F8FAFC;">
        <div style="width:280px;background:#fff;border-right:1px solid #E2E8F0;display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;border-bottom:1px solid #E2E8F0;">
                <span style="font-size:0.75rem;font-weight:700;color:#64748B;letter-spacing:0.5px;">SERVICES</span>
                <div style="display:flex;gap:5px;">
                    <button id="btnViewArchived" style="background:#F1F5F9;color:#64748B;border:1px solid #E2E8F0;height:24px;border-radius:4px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.75rem;padding:0 8px;font-weight:600;" title="View Archived Services">Archived</button>
                    <button id="btnAddService" style="background:#F97316;color:#fff;border:none;width:24px;height:24px;border-radius:4px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-weight:bold;" title="Add New Service">+</button>
                </div>
            </div>
            <div style="flex:1;overflow-y:auto;">
                ${serviceListHtml}
            </div>
        </div>
        <div style="flex:1;display:flex;flex-direction:column;padding:25px;gap:20px;overflow-y:auto;">
            ${rightContentHtml}
        </div>
    </div>`;
},

renderTransactions: (container, txns = []) => {
const rows = txns.map(t => {
    const servicesSummary = Array.isArray(t.items)
        ? t.items.map(i => `${i.serviceName || i.name || '?'} (${i.variantLabel || ''}) x${i.qty || 1}`).join('<br>')
        : (t.items || '—');
    const statusColor = t.status === 'Pending' ? { bg: '#FEF9C3', text: '#CA8A04' } : { bg: '#DCFCE7', text: '#15803D' };
    return `
<tr>
    <td style="font-weight:700;color:#0F172A;white-space:nowrap;">#${t.orderNum || t.id || '—'}</td>
    <td style="color:#64748B;white-space:nowrap;">${t.date || '—'}</td>
    <td style="color:#64748B;white-space:nowrap;">${t.time || '—'}</td>
    <td style="color:#0F172A;">${t.customer || 'Walk-in'}</td>
    <td style="color:#64748B;">${t.contact || '—'}</td>
    <td style="color:#475569;font-size:0.82rem;">${servicesSummary}</td>
    <td style="font-weight:600;color:#16A34A;white-space:nowrap;">₱${parseFloat(t.total || 0).toFixed(2)}</td>
    <td style="color:#0F172A;white-space:nowrap;">₱${parseFloat(t.amountPaid || 0).toFixed(2)}</td>
    <td style="color:#0F172A;white-space:nowrap;">₱${parseFloat(t.change || 0).toFixed(2)}</td>
    <td style="color:#64748B;white-space:nowrap;">${t.paymentMethod || '—'}</td>
    <td>
        <span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:0.8rem;font-weight:600;background:${statusColor.bg};color:${statusColor.text};">${t.status || 'Completed'}</span>
    </td>
    <td>
        <button class="btn-admin-view-receipt" data-ordernum="${t.orderNum || t.id}" style="background:#0F172A;color:#fff;border:none;border-radius:5px;padding:4px 12px;font-size:0.78rem;font-weight:600;cursor:pointer;white-space:nowrap;">View Receipt</button>
    </td>
</tr>`;
}).join('');

container.innerHTML = `
<div class="admin-card">
    <div class="admin-card-title d-flex justify-content-between align-items-center">
        <span>Transaction History</span>
        <button class="btn btn-sm btn-success" id="btnExportTxnCSV" style="background:#10B981;border-color:#10B981;">⬇ Export CSV</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered w-100" id="transactionsTable" style="font-size:0.85rem;">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Contact No.</th>
                    <th>Services Summary</th>
                    <th>Total Amount</th>
                    <th>Cash Received</th>
                    <th>Change</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>
</div>
`;

if (typeof $ !== 'undefined' && $.fn.DataTable) {
    $('#transactionsTable').DataTable({
        order: [[1, 'desc'], [2, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        scrollX: true,
        columnDefs: [{ targets: [-1], orderable: false }],
        language: {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ transactions',
            infoEmpty: 'No transactions available',
            emptyTable: 'No transactions found.',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        }
    });

    // Event delegation for View Receipt buttons (works with DataTables pagination)
    document.querySelector('#transactionsTable tbody').addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-admin-view-receipt');
        if (!btn) return;
        const orderNum = btn.getAttribute('data-ordernum');
        const txn = OrderModel.getTransactionByNum(orderNum);
        if (txn) AdminView.showAdminReceiptModal(txn);
    });

    document.getElementById('btnExportTxnCSV').addEventListener('click', () => {
        const headers = ['Order #', 'Date', 'Time', 'Customer', 'Contact No.', 'Services Summary', 'Total Amount', 'Cash Received', 'Change', 'Payment Method', 'Status'];
        const csvRows = [headers.join(',')];
        txns.forEach(t => {
            const summary = Array.isArray(t.items)
                ? t.items.map(i => `${i.serviceName || '?'} (${i.variantLabel || ''}) x${i.qty || 1}`).join(' | ')
                : (t.items || '');
            csvRows.push([
                `"#${t.orderNum || t.id || ''}"`,
                `"${t.date || ''}"`,
                `"${t.time || ''}"`,
                `"${t.customer || 'Walk-in'}"`,
                `"${t.contact || ''}"`,
                `"${summary}"`,
                `"₱${parseFloat(t.total || 0).toFixed(2)}"`,
                `"₱${parseFloat(t.amountPaid || 0).toFixed(2)}"`,
                `"₱${parseFloat(t.change || 0).toFixed(2)}"`,
                `"${t.paymentMethod || ''}"`,
                `"${t.status || 'Completed'}"`
            ].join(','));
        });
        const blob = new Blob([csvRows.join('\n')], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    });
}
},

showAdminReceiptModal: (txn) => {
    document.getElementById('adminReceiptModalTitle').textContent = `Receipt – #${txn.orderNum}`;
    document.getElementById('adminReceiptModalSub').textContent = `Transaction #${txn.orderNum}`;
    const shopInfo = ServiceModel.getShopInfo();
    const text = OrderView.buildReceiptText(
        txn.items,
        txn.total,
        shopInfo,
        txn.date,
        txn.time,
        '#' + txn.orderNum,
        txn
    );
    document.getElementById('adminReceiptModalBody').textContent = text;
    const modal = new bootstrap.Modal(document.getElementById('adminReceiptModal'));
    modal.show();
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
},

renderReports: (container) => {
const today = new Date().toISOString().split('T')[0];

container.innerHTML = `
<div style="display:flex;flex-direction:column;gap:16px;">

    <!-- Date Filter Bar -->
    <div
        style="display:flex;align-items:center;gap:10px;background:#fff;padding:10px 16px;border:1px solid #E2E8F0;border-radius:8px;">
        <span style="font-size:0.85rem;color:#64748B;font-weight:500;">From</span>
        <input type="date" id="reportFrom" value=""
            style="border:1px solid #CBD5E1;border-radius:6px;padding:5px 10px;font-size:0.85rem;color:#0F172A;outline:none;">
        <span style="font-size:0.85rem;color:#64748B;">—</span>
        <span style="font-size:0.85rem;color:#64748B;font-weight:500;">To</span>
        <input type="date" id="reportTo" value="${today}"
            style="border:1px solid #CBD5E1;border-radius:6px;padding:5px 10px;font-size:0.85rem;color:#0F172A;outline:none;">
        <span style="flex:1;font-size:0.8rem;color:#94A3B8;text-align:right;font-style:italic;" id="reportHint">Select a
            date range to generate report</span>
        <button id="btnGenerateReport"
            style="background:#F97316;color:#fff;border:none;border-radius:6px;padding:7px 20px;font-size:0.85rem;font-weight:600;cursor:pointer;white-space:nowrap;transition:background 0.2s;">
            Generate Report
        </button>
    </div>

    <!-- Stat Cards Row -->
    <div style="display:flex;gap:16px;align-items:stretch;">

        <!-- Total Sales -->
        <div
            style="flex:1;background:#fff;border:1px solid #E2E8F0;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid #F1F5F9;">
                <div
                    style="width:32px;height:32px;border-radius:50%;background:#DCFCE7;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:1rem;font-weight:700;color:#16A34A;">₱</span>
                </div>
                <span style="font-weight:700;font-size:0.95rem;color:#0F172A;">Total Sales</span>
            </div>
            <div style="flex:1;display:flex;align-items:center;justify-content:flex-start;padding:120px 30px;">
                <span id="reportTotalSales" style="font-size:3.5rem;font-weight:700;color:#16A34A;">₱0.00</span>
            </div>
            <div style="padding:12px 18px;border-top:1px solid #F1F5F9;">
                <span style="font-size:0.85rem;color:#94A3B8;">Revenue in selected range</span>
            </div>
        </div>

        <!-- Total Transactions -->
        <div
            style="flex:1;background:#fff;border:1px solid #E2E8F0;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid #F1F5F9;">
                <div
                    style="width:32px;height:32px;border-radius:50%;background:#DBEAFE;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:1rem;font-weight:700;color:#2563EB;">#</span>
                </div>
                <span style="font-weight:700;font-size:0.95rem;color:#0F172A;">Total Transactions</span>
            </div>
            <div style="flex:1;display:flex;align-items:center;justify-content:flex-start;padding:120px 30px;">
                <span id="reportTotalTxns" style="font-size:3.5rem;font-weight:700;color:#2563EB;">0</span>
            </div>
            <div style="padding:12px 18px;border-top:1px solid #F1F5F9;">
                <span style="font-size:0.85rem;color:#94A3B8;">Completed transactions</span>
            </div>
        </div>

        <!-- Top Service -->
        <div
            style="flex:1;background:#fff;border:1px solid #E2E8F0;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid #F1F5F9;">
                <div
                    style="width:32px;height:32px;border-radius:50%;background:#FEF9C3;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:1rem;font-weight:700;color:#CA8A04;">★</span>
                </div>
                <span style="font-weight:700;font-size:0.95rem;color:#0F172A;">Top Service</span>
            </div>
            <div style="flex:1;display:flex;align-items:center;justify-content:flex-start;padding:120px 30px;">
                <span id="reportTopService" style="font-size:3.5rem;font-weight:700;color:#CA8A04;">—</span>
            </div>
            <div style="padding:12px 18px;border-top:1px solid #F1F5F9;">
                <span style="font-size:0.85rem;color:#94A3B8;">No data for this range</span>
            </div>
        </div>

    </div>
</div>
`;

document.getElementById('btnGenerateReport').addEventListener('click', () => {
const fromVal = document.getElementById('reportFrom').value;
const toVal = document.getElementById('reportTo').value;

if (!fromVal || !toVal) {
document.getElementById('reportHint').textContent = '⚠ Please select both From and To dates.';
document.getElementById('reportHint').style.color = '#EF4444';
return;
}

const from = new Date(fromVal);
const to = new Date(toVal);
to.setHours(23, 59, 59);

if (from > to) {
document.getElementById('reportHint').textContent = '⚠ "From" date must be before "To" date.';
document.getElementById('reportHint').style.color = '#EF4444';
return;
}

const txns = typeof OrderModel !== 'undefined' ? OrderModel.getTransactions() : [];
const filtered = txns.filter(t => {
const d = new Date(t.date || t.createdAt || Date.now());
return d >= from && d <= to;
});
const totalSales = filtered.reduce((sum, t) => sum + (t.total || 0), 0);

    const svcCount = {};
    filtered.forEach(t => {
        (t.items || []).forEach(item => {
            const sName = item.serviceName || item.name || 'Unknown';
            svcCount[sName] = (svcCount[sName] || 0) + (item.qty || 1);
        });
    });
    const topSvc = Object.entries(svcCount).sort((a, b) => b[1] - a[1])[0];

    document.getElementById('reportTotalSales').textContent = '₱' + totalSales.toFixed(2);
    document.getElementById('reportTotalTxns').textContent = filtered.length;
    document.getElementById('reportTopService').textContent = topSvc ? topSvc[0] : '—';
    document.getElementById('reportHint').textContent = `Report for ${fromVal} → ${toVal}`;
    document.getElementById('reportHint').style.color = '#16A34A';
    });
    },

    renderActivityLog: (container, logs = []) => {
    const rows = logs.map(log => `
    <tr>
        <td style="color:#64748B;font-size:0.85rem;white-space:nowrap;">${log.date || '—'}</td>
        <td style="color:#64748B;font-size:0.85rem;white-space:nowrap;">${log.time || '—'}</td>
        <td>
            <span style="
                    display:inline-block;
                    padding:2px 10px;
                    border-radius:12px;
                    font-size:0.8rem;
                    font-weight:600;
                    background:${log.role === 'Admin' ? '#DBEAFE' : '#FEF3C7'};
                    color:${log.role === 'Admin' ? '#1D4ED8' : '#D97706'};
                ">${log.role || 'User'}</span>
        </td>
        <td style="font-weight:600;color:#0F172A;">${log.action}</td>
        <td style="color:#475569;font-size:0.9rem;">${log.details || '—'}</td>
    </tr>
    `).join('');

    container.innerHTML = `
    <div class="admin-card">
        <div class="admin-card-title d-flex justify-content-between align-items-center">
            <span>Activity Log</span>
            <button class="btn btn-sm btn-success" id="btnExportLogCSV"
                style="background:#10B981;border-color:#10B981;color:#fff;">⬇ Export Log</button>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered w-100" id="activityLogTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    </div>
    `;

    if (typeof $ !== 'undefined' && $.fn.DataTable) {
    $('#activityLogTable').DataTable({
    order: [[0, 'desc'], [1, 'desc']],
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    language: {
    search: 'Search:',
    lengthMenu: 'Show _MENU_ entries',
    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
    infoEmpty: 'No activity logs available',
    emptyTable: 'No activity logs found.',
    paginate: { first: '«', last: '»', next: '›', previous: '‹' }
    }
    });

    document.getElementById('btnExportLogCSV').addEventListener('click', () => {
    const headers = ['Date', 'Time', 'Role', 'Action', 'Details'];
    const csvRows = [headers.join(',')];
    logs.forEach(log => {
    csvRows.push([
    `"${log.date || ''}"`,
    `"${log.time || ''}"`,
    `"${log.role || ''}"`,
    `"${log.action || ''}"`,
    `"${log.details || ''}"`
    ].join(','));
    });
    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `activity_log_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    });
    }
    },

    renderUsers: (container, users = []) => {
        // Cache for client-side filtering
        window._usersData = users;

        function buildRows(list) {
            if (!list.length) return '<tr><td colspan="5" style="text-align:center;color:#94A3B8;padding:30px;">No users found.</td></tr>';
            return list.map(u => {
                const isAdmin = u.role === 'Admin';
                const isActive = u.status === 'Active';
                const roleBg    = isAdmin ? '#EFF6FF' : '#F0FDF4';
                const roleColor = isAdmin ? '#1D4ED8' : '#15803D';
                const statusBg    = isActive ? '#DCFCE7' : '#F1F5F9';
                const statusColor = isActive ? '#15803D' : '#6B7280';

                const actions = isAdmin
                    ? `<span style="color:#94A3B8;font-size:0.8rem;font-style:italic;">Owner — no actions</span>`
                    : `<div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <button class="um-btn um-edit" data-id="${u.id}" style="background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;border-radius:6px;padding:4px 10px;font-size:0.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
                        </button>
                        <button class="um-btn um-reset" data-id="${u.id}" data-email="${u.email}" style="background:#FFFBEB;color:#D97706;border:1px solid #FDE68A;border-radius:6px;padding:4px 10px;font-size:0.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Reset PW
                        </button>
                        <button class="um-btn um-toggle" data-id="${u.id}" data-status="${u.status}" style="background:${isActive ? '#FEF2F2' : '#F0FDF4'};color:${isActive ? '#DC2626' : '#16A34A'};border:1px solid ${isActive ? '#FECACA' : '#BBF7D0'};border-radius:6px;padding:4px 10px;font-size:0.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;">
                            ${isActive
                                ? `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>Deactivate`
                                : `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Activate`
                            }
                        </button>

                    </div>`;

                return `<tr>
                    <td style="color:#0F172A;">${u.email}</td>
                    <td><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;background:${roleBg};color:${roleColor};">${u.role}</span></td>
                    <td><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;background:${statusBg};color:${statusColor};">${u.status}</span></td>
                    <td style="color:#64748B;white-space:nowrap;font-size:0.85rem;">${u.createdAt}</td>
                    <td>${actions}</td>
                </tr>`;
            }).join('');
        }

        container.innerHTML = `
        <div style="display:flex;flex-direction:column;gap:18px;">

            <!-- Header -->
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <h2 style="font-size:1.4rem;font-weight:700;color:#0F172A;margin:0 0 3px;">Manage Users</h2>
                    <p style="color:#64748B;font-size:0.85rem;margin:0;">Manage cashier accounts and system access.</p>
                </div>
                <button id="btnOpenAddCashier" style="display:flex;align-items:center;gap:8px;background:#F97316;color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:0.88rem;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(249,115,22,0.3);transition:all 0.2s;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Cashier
                </button>
            </div>



            <!-- Table -->
            <div class="admin-card" style="padding:0;overflow:hidden;">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" id="usersTable" style="font-size:0.875rem;">
                        <thead style="background:#F8FAFC;">
                            <tr>
                                <th style="color:#64748B;font-weight:600;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;padding:14px 16px;border-bottom:2px solid #E2E8F0;">Email</th>
                                <th style="color:#64748B;font-weight:600;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;padding:14px 16px;border-bottom:2px solid #E2E8F0;">Role</th>
                                <th style="color:#64748B;font-weight:600;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;padding:14px 16px;border-bottom:2px solid #E2E8F0;">Status</th>
                                <th style="color:#64748B;font-weight:600;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;padding:14px 16px;border-bottom:2px solid #E2E8F0;">Created At</th>
                                <th style="color:#64748B;font-weight:600;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;padding:14px 16px;border-bottom:2px solid #E2E8F0;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">${buildRows(users)}</tbody>
                    </table>
                </div>
            </div>
        </div>`;

        // ── DataTable (disable last 2 columns sort) ──────────────────────
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#usersTable').DataTable({
                order: [[0, 'asc']],
                pageLength: 10,
                lengthMenu: [5, 10, 25],
                scrollX: true,
                columnDefs: [{ targets: [4], orderable: false }],
                language: { emptyTable: 'No users found.', paginate: { first: '«', last: '»', next: '›', previous: '‹' } },
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>t<"d-flex justify-content-between align-items-center mt-2"ip>',
            });
        }



        // Helper: show toast
        function toast(msg, color = '#16A34A') {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg, showConfirmButton: false, timer: 2200, timerProgressBar: true });
        }
        function errToast(msg) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg, showConfirmButton: false, timer: 3000 });
        }

        // Helper: reload the page section with fresh data
        async function reloadUsers() {
            const res  = await fetch('api/users.php');
            const data = await res.json();
            if (data.success) {
                window._usersData = data.users;
                if ($.fn.DataTable.isDataTable('#usersTable')) $('#usersTable').DataTable().destroy();
                document.getElementById('usersTableBody').innerHTML = buildRows(data.users);

                if (typeof $ !== 'undefined' && $.fn.DataTable) {
                    $('#usersTable').DataTable({ order: [[0,'asc']], pageLength: 10, lengthMenu:[5,10,25], scrollX:true, columnDefs:[{targets:[4],orderable:false}], language:{emptyTable:'No users found.',paginate:{first:'«',last:'»',next:'›',previous:'‹'}} });
                }
            }
        }

        // ── Open Add Cashier modal ────────────────────────────────────────
        document.getElementById('btnOpenAddCashier').addEventListener('click', () => {
            document.getElementById('addEmail').value = '';
            document.getElementById('addPassword').value = '';
            document.getElementById('addConfirmPassword').value = '';
            document.getElementById('addStatus').value = 'Active';
            new bootstrap.Modal(document.getElementById('addCashierModal')).show();
        });

        // ── Save Add Cashier ──────────────────────────────────────────────
        document.getElementById('btnSaveAddCashier').onclick = async () => {
            const email    = document.getElementById('addEmail').value.trim();
            const password = document.getElementById('addPassword').value;
            const confirm  = document.getElementById('addConfirmPassword').value;
            const status   = document.getElementById('addStatus').value;
            if (!email || !password) return errToast('Email and password are required.');
            if (password !== confirm) return errToast('Passwords do not match.');
            if (password.length < 4)  return errToast('Password must be at least 4 characters.');
            const res  = await fetch('api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'create', email, password, status}) });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addCashierModal')).hide();
                toast('Cashier account created!');
                await reloadUsers();
            } else errToast(data.message || 'Failed to create user.');
        };

        // ── Table action buttons (event delegation) ───────────────────────
        document.getElementById('usersTableBody').addEventListener('click', async (e) => {
            const btn = e.target.closest('.um-btn');
            if (!btn) return;
            const id    = parseInt(btn.dataset.id);
            const email = btn.dataset.email || '';

            // ── EDIT ──────────────────────────────────────────────────────
            if (btn.classList.contains('um-edit')) {
                const user = window._usersData.find(u => u.id === id);
                if (!user) return;
                document.getElementById('editUserId').value = id;
                document.getElementById('editEmail').value  = user.email;
                document.getElementById('editStatus').value = user.status;
                new bootstrap.Modal(document.getElementById('editCashierModal')).show();

                document.getElementById('btnSaveEditCashier').onclick = async () => {
                    const newEmail  = document.getElementById('editEmail').value.trim();
                    const newStatus = document.getElementById('editStatus').value;
                    if (!newEmail) return errToast('Email is required.');
                    const res  = await fetch('api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'update', id, email: newEmail, status: newStatus}) });
                    const data = await res.json();
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editCashierModal')).hide();
                        toast('Cashier updated successfully!');
                        await reloadUsers();
                    } else errToast(data.message || 'Failed to update.');
                };
            }

            // ── RESET PASSWORD ────────────────────────────────────────────
            if (btn.classList.contains('um-reset')) {
                document.getElementById('resetUserId').value        = id;
                document.getElementById('resetUserEmail').textContent = email;
                document.getElementById('resetNewPassword').value   = '';
                document.getElementById('resetConfirmPassword').value = '';
                new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();

                document.getElementById('btnSaveResetPassword').onclick = async () => {
                    const pw  = document.getElementById('resetNewPassword').value;
                    const cpw = document.getElementById('resetConfirmPassword').value;
                    if (!pw) return errToast('New password is required.');
                    if (pw !== cpw) return errToast('Passwords do not match.');
                    if (pw.length < 4) return errToast('Password must be at least 4 characters.');
                    const res  = await fetch('api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'reset_password', id, password: pw}) });
                    const data = await res.json();
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
                        toast('Password reset successfully!');
                    } else errToast(data.message || 'Failed to reset password.');
                };
            }

            // ── TOGGLE STATUS ─────────────────────────────────────────────
            if (btn.classList.contains('um-toggle')) {
                const currentStatus = btn.dataset.status;
                const newStatus     = currentStatus === 'Active' ? 'Inactive' : 'Active';
                const verb          = newStatus === 'Active' ? 'activate' : 'deactivate';
                const result = await Swal.fire({
                    title: `${verb.charAt(0).toUpperCase() + verb.slice(1)} user?`,
                    text: `Are you sure you want to ${verb} this cashier account?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: newStatus === 'Active' ? '#16A34A' : '#DC2626',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: `Yes, ${verb}`,
                });
                if (!result.isConfirmed) return;
                const res  = await fetch('api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'toggle_status', id, status: newStatus}) });
                const data = await res.json();
                if (data.success) { toast(`Account ${newStatus.toLowerCase()}d!`); await reloadUsers(); }
                else errToast(data.message || 'Failed to update status.');
            }


        });
    },

    };
    })();

</script>
