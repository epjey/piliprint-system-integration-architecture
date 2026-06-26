<script>
// ============================================================
// CONTROLLER: AdminController.js (served as .php)
// Handles logic for the admin dashboard side
// ============================================================

// State variables for Admin Services management
let adminServicesState = [];
let currentAdminServiceId = null;
let currentAdminTab = 'variants';

document.addEventListener('DOMContentLoaded', () => {
    initAdmin();
});

// Helper: handle icon file selection (used in Add Service dialog)
function handleIconFile(file, previewDiv, previewImg, placeholderDiv, dropZone) {
    if (!file.type.startsWith('image/')) {
        Swal.showValidationMessage('Please select an image file');
        return;
    }
    if (file.size > 512000) {
        Swal.showValidationMessage('Image must be under 500KB');
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImg.src = e.target.result;
        previewDiv.style.display = 'block';
        placeholderDiv.innerHTML = `
            <div style="font-size:0.8rem;color:#16A34A;margin-top:8px;">✓ ${file.name}</div>
            <div style="font-size:0.75rem;color:#94A3B8;margin-top:2px;">Click to change</div>
        `;
        dropZone.style.borderColor = '#16A34A';
    };
    reader.readAsDataURL(file);
}

function initAdminServicesState() {
    if (typeof ServiceModel !== 'undefined') {
        adminServicesState = ServiceModel.getAll().map(s => {
            let optionGroups = [];
            if (s.optionGroups) {
                optionGroups = s.optionGroups.map(g => ({
                    id: g.id,
                    name: g.label,
                    type: g.id === 'thickness' ? 'Page Tier' : 'Choice',
                    options: g.options ? g.options.map(o => ({
                        id: o.id,
                        name: o.label,
                        price: o.price
                    })) : []
                }));
            }
            return {
                id: s.id,
                name: s.label,
                icon: s.icon,
                variants: s.variants ? s.variants.map(v => ({ id: v.id, name: v.label, basePrice: v.price })) : [],
                optionGroups: optionGroups,
                get variantsCount() { return this.variants.length; },
                get optionsCount() { return this.optionGroups.length; }
            };
        });
        if (adminServicesState.length > 0) {
            currentAdminServiceId = adminServicesState[0].id;
        }
    }
}

function updatePricingSimulationUI(container, service) {
    if (!window.pricingSimulationState || !service) return;
    
    // 1. Highlight selected variant button
    const variantBtns = container.querySelectorAll('.sim-btn-variant');
    variantBtns.forEach(btn => {
        const isSelected = btn.getAttribute('data-id') == window.pricingSimulationState.selectedVariantId;
        if (isSelected) {
            btn.style.background = '#4A7FB5';
            btn.style.color = '#fff';
            btn.style.borderColor = '#4A7FB5';
        } else {
            btn.style.background = '#fff';
            btn.style.color = '#0F172A';
            btn.style.borderColor = '#CBD5E1';
        }
    });
    
    // 2. Highlight selected options
    const optionBtns = container.querySelectorAll('.sim-btn-option');
    optionBtns.forEach(btn => {
        const groupId = btn.getAttribute('data-group-id');
        const optionId = btn.getAttribute('data-option-id');
        const isSelected = window.pricingSimulationState.selectedOptionIds[groupId] == optionId;
        if (isSelected) {
            btn.style.background = '#D97706';
            btn.style.color = '#fff';
            btn.style.borderColor = '#D97706';
        } else {
            btn.style.background = '#fff';
            btn.style.color = '#0F172A';
            btn.style.borderColor = '#CBD5E1';
        }
    });
    
    // 3. Recalculate price
    const selectedVariant = service.variants.find(v => v.id == window.pricingSimulationState.selectedVariantId);
    let basePrice = selectedVariant ? parseFloat(selectedVariant.basePrice || 0) : 0;
    let addOnPrice = 0;
    let breakdownParts = [`Base: ₱${basePrice.toFixed(1)}`];
    
    if (service.optionGroups) {
        service.optionGroups.forEach(g => {
            const selectedOptId = window.pricingSimulationState.selectedOptionIds[g.id];
            if (selectedOptId && g.options) {
                const opt = g.options.find(o => o.id == selectedOptId);
                if (opt && parseFloat(opt.price || 0) > 0) {
                    addOnPrice += parseFloat(opt.price);
                    breakdownParts.push(`${g.name}: ₱${parseFloat(opt.price).toFixed(1)}`);
                }
            }
        });
    }
    
    const totalPrice = basePrice + addOnPrice;
    
    const totalEl = container.querySelector('#pricingSimTotal');
    if (totalEl) {
        totalEl.textContent = `₱${totalPrice.toFixed(2)}`;
    }
    
    const breakdownEl = container.querySelector('#pricingSimBreakdown');
    if (breakdownEl) {
        breakdownEl.textContent = breakdownParts.join(' + ');
    }
}

function refreshServicesUI() {
    const bodyEl = document.getElementById('adminContentBody');
    if (!bodyEl) return;
    
    // Render view
    AdminView.renderServices(bodyEl, adminServicesState, currentAdminServiceId, currentAdminTab);
    
    const currentService = adminServicesState.find(s => s.id == currentAdminServiceId);
    
    // Bind service selection
    const serviceItems = bodyEl.querySelectorAll('.service-list-item');
    serviceItems.forEach(item => {
        item.addEventListener('click', (e) => {
            currentAdminServiceId = e.currentTarget.getAttribute('data-id');
            refreshServicesUI();
        });
    });
    
    // Bind tab switching
    const tabs = bodyEl.querySelectorAll('.admin-service-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            currentAdminTab = e.currentTarget.getAttribute('data-tab');
            refreshServicesUI();
        });
    });
    
    // Bind Edit Icon button
    const btnEditIcon = bodyEl.querySelector('#btnEditServiceIcon');
    if (btnEditIcon && currentService) {
        btnEditIcon.addEventListener('click', () => {
            Swal.fire({
                title: 'Change Service Icon',
                html: `
                    <div style="text-align:left; font-family:'Segoe UI',sans-serif;">
                        <div id="swalIconDropZone" style="border:2px dashed #CBD5E1;border-radius:8px;padding:20px;text-align:center;cursor:pointer;transition:all 0.2s;background:#F8FAFC;">
                            <div id="swalIconPreview" style="display:none;margin-bottom:10px;">
                                <img id="swalIconPreviewImg" src="" style="max-width:64px;max-height:64px;border-radius:6px;border:1px solid #E2E8F0;" />
                            </div>
                            <div id="swalIconPlaceholder">
                                <div style="font-size:1.5rem;margin-bottom:5px;">📁</div>
                                <div style="font-size:0.85rem;color:#64748B;">Click to browse or drag & drop an image</div>
                                <div style="font-size:0.75rem;color:#94A3B8;margin-top:3px;">PNG, JPG, SVG (max 500KB)</div>
                            </div>
                            <input type="file" id="swalServiceIconFile" accept="image/*" style="display:none;" />
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonColor: '#4A7FB5',
                confirmButtonText: 'Update Icon',
                didOpen: () => {
                    const dropZone = document.getElementById('swalIconDropZone');
                    const fileInput = document.getElementById('swalServiceIconFile');
                    const preview = document.getElementById('swalIconPreview');
                    const previewImg = document.getElementById('swalIconPreviewImg');
                    const placeholder = document.getElementById('swalIconPlaceholder');
                    
                    // Click to browse
                    dropZone.addEventListener('click', () => fileInput.click());
                    
                    // File selected
                    fileInput.addEventListener('change', (e) => {
                        if (e.target.files && e.target.files[0]) {
                            handleIconFile(e.target.files[0], preview, previewImg, placeholder, dropZone);
                        }
                    });
                    
                    // Drag & drop
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropZone.style.borderColor = '#4A7FB5';
                        dropZone.style.background = '#EFF6FF';
                    });
                    dropZone.addEventListener('dragleave', () => {
                        dropZone.style.borderColor = '#CBD5E1';
                        dropZone.style.background = '#F8FAFC';
                    });
                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropZone.style.borderColor = '#CBD5E1';
                        dropZone.style.background = '#F8FAFC';
                        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                            handleIconFile(e.dataTransfer.files[0], preview, previewImg, placeholder, dropZone);
                        }
                    });
                },
                preConfirm: () => {
                    const previewImg = document.getElementById('swalIconPreviewImg');
                    if (previewImg.src && previewImg.src !== '' && !previewImg.src.endsWith('admin.php')) {
                        return previewImg.src;
                    }
                    Swal.showValidationMessage('Please select an image first');
                    return false;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    currentService.icon = result.value;
                    refreshServicesUI();
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Service icon updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
    }
    
    // Bind Archive Service button
    const btnArchiveService = bodyEl.querySelector('#btnArchiveService');
    if (btnArchiveService && currentService) {
        btnArchiveService.addEventListener('click', () => {
            Swal.fire({
                title: 'Archive Service?',
                text: `Are you sure you want to archive "${currentService.name}"? It will be hidden from the active list.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#94A3B8',
                confirmButtonText: 'Yes, Archive It'
            }).then((result) => {
                if (result.isConfirmed) {
                    currentService.isArchived = true;
                    // Reset selection to the first available active service
                    currentAdminServiceId = null; 
                    refreshServicesUI();
                    Swal.fire('Archived!', `${currentService.name} has been archived.`, 'success');
                }
            });
        });
    }

    // Bind View Archived button
    const btnViewArchived = bodyEl.querySelector('#btnViewArchived');
    if (btnViewArchived) {
        btnViewArchived.addEventListener('click', () => {
            const archivedServices = adminServicesState.filter(s => s.isArchived);
            if (archivedServices.length === 0) {
                Swal.fire('No Archived Services', 'You do not have any archived services.', 'info');
                return;
            }

            let htmlList = `<div style="text-align:left;max-height:300px;overflow-y:auto;border:1px solid #E2E8F0;border-radius:6px;padding:10px;">`;
            archivedServices.forEach(s => {
                htmlList += `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;border-bottom:1px solid #F1F5F9;">
                        <div style="font-weight:600;font-size:0.9rem;">${s.name}</div>
                        <button class="btn-restore-service" data-id="${s.id}" style="background:#4A7FB5;color:#fff;border:none;padding:5px 12px;border-radius:4px;font-size:0.75rem;cursor:pointer;">Restore</button>
                    </div>
                `;
            });
            htmlList += `</div>`;

            Swal.fire({
                title: 'Archived Services',
                html: htmlList,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Close',
                didOpen: () => {
                    const restoreBtns = document.querySelectorAll('.btn-restore-service');
                    restoreBtns.forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const sid = e.currentTarget.getAttribute('data-id');
                            const srv = adminServicesState.find(s => s.id == sid);
                            if (srv) {
                                srv.isArchived = false;
                                currentAdminServiceId = srv.id; // Switch to the restored service
                                Swal.close();
                                refreshServicesUI();
                                Swal.fire('Restored!', `${srv.name} has been restored.`, 'success');
                            }
                        });
                    });
                }
            });
        });
    }

    // Bind Add Service button
    const btnAddService = bodyEl.querySelector('#btnAddService');
    if (btnAddService) {
        btnAddService.addEventListener('click', () => {
            Swal.fire({
                title: 'Add New Service',
                html: `
                    <div style="text-align:left; font-family:'Segoe UI',sans-serif;">
                        <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Service Name</label>
                        <input id="swalServiceName" class="swal2-input" placeholder="e.g. Sticker Printing" style="margin:0 0 15px;width:100%;box-sizing:border-box;">
                        
                        <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:8px;">Service Icon</label>
                        <div id="swalIconDropZone" style="border:2px dashed #CBD5E1;border-radius:8px;padding:20px;text-align:center;cursor:pointer;transition:all 0.2s;background:#F8FAFC;">
                            <div id="swalIconPreview" style="display:none;margin-bottom:10px;">
                                <img id="swalIconPreviewImg" src="" style="max-width:64px;max-height:64px;border-radius:6px;border:1px solid #E2E8F0;" />
                            </div>
                            <div id="swalIconPlaceholder">
                                <div style="font-size:1.5rem;margin-bottom:5px;">📁</div>
                                <div style="font-size:0.85rem;color:#64748B;">Click to browse or drag & drop an image</div>
                                <div style="font-size:0.75rem;color:#94A3B8;margin-top:3px;">PNG, JPG, SVG (max 500KB)</div>
                            </div>
                            <input type="file" id="swalServiceIconFile" accept="image/*" style="display:none;" />
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonColor: '#4A7FB5',
                confirmButtonText: 'Create Service',
                didOpen: () => {
                    const dropZone = document.getElementById('swalIconDropZone');
                    const fileInput = document.getElementById('swalServiceIconFile');
                    const preview = document.getElementById('swalIconPreview');
                    const previewImg = document.getElementById('swalIconPreviewImg');
                    const placeholder = document.getElementById('swalIconPlaceholder');
                    
                    // Click to browse
                    dropZone.addEventListener('click', () => fileInput.click());
                    
                    // File selected
                    fileInput.addEventListener('change', (e) => {
                        if (e.target.files && e.target.files[0]) {
                            handleIconFile(e.target.files[0], preview, previewImg, placeholder, dropZone);
                        }
                    });
                    
                    // Drag & drop
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropZone.style.borderColor = '#4A7FB5';
                        dropZone.style.background = '#EFF6FF';
                    });
                    dropZone.addEventListener('dragleave', () => {
                        dropZone.style.borderColor = '#CBD5E1';
                        dropZone.style.background = '#F8FAFC';
                    });
                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropZone.style.borderColor = '#CBD5E1';
                        dropZone.style.background = '#F8FAFC';
                        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                            handleIconFile(e.dataTransfer.files[0], preview, previewImg, placeholder, dropZone);
                        }
                    });
                },
                preConfirm: () => {
                    const name = document.getElementById('swalServiceName').value.trim();
                    if (!name) {
                        Swal.showValidationMessage('Please enter a service name');
                        return false;
                    }
                    const previewImg = document.getElementById('swalIconPreviewImg');
                    const icon = previewImg.src && previewImg.src !== '' && !previewImg.src.endsWith('admin.php') ? previewImg.src : '📦';
                    return { name, icon };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newId = 'service_' + Date.now();
                    const newService = {
                        id: newId,
                        name: result.value.name,
                        icon: result.value.icon,
                        variants: [],
                        optionGroups: [],
                        get variantsCount() { return this.variants.length; },
                        get optionsCount() { return this.optionGroups.length; }
                    };
                    adminServicesState.push(newService);
                    currentAdminServiceId = newId;
                    currentAdminTab = 'variants';
                    refreshServicesUI();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Created!',
                        text: `Service "${result.value.name}" added successfully.`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
    }
    
    // Bind Add Variant/Option button
    const btnAddVariant = bodyEl.querySelector('#btnAddVariant');
    if (btnAddVariant) {
        btnAddVariant.addEventListener('click', () => {
            if (!currentService) return;
            
            const isVariantTab = currentAdminTab === 'variants';
            if (isVariantTab) {
                // Add Variant
                Swal.fire({
                    title: 'Add Variant',
                    html: `
                        <div style="text-align:left; font-family:'Segoe UI',sans-serif;">
                            <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Variant Name</label>
                            <input id="swalVariantName" class="swal2-input" placeholder="e.g. Matte Cover" style="margin:0 0 15px;width:100%;box-sizing:border-box;">
                            
                            <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Base Price (₱)</label>
                            <input id="swalVariantPrice" type="number" step="0.01" class="swal2-input" placeholder="0.00" style="margin:0;width:100%;box-sizing:border-box;">
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonColor: '#4A7FB5',
                    confirmButtonText: 'Add Variant',
                    preConfirm: () => {
                        const name = document.getElementById('swalVariantName').value.trim();
                        const priceVal = document.getElementById('swalVariantPrice').value.trim();
                        if (!name) {
                            Swal.showValidationMessage('Please enter a variant name');
                            return false;
                        }
                        const price = parseFloat(priceVal);
                        if (isNaN(price) || price < 0) {
                            Swal.showValidationMessage('Please enter a valid price');
                            return false;
                        }
                        return { name, price };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        currentService.variants.push({
                            id: 'var_' + Date.now(),
                            name: result.value.name,
                            basePrice: result.value.price
                        });
                        refreshServicesUI();
                        Swal.fire({ icon: 'success', title: 'Added!', timer: 1000, showConfirmButton: false });
                    }
                });
            } else {
                // Add Option Group
                Swal.fire({
                    title: 'Add Option Group',
                    html: `
                        <div style="text-align:left; font-family:'Segoe UI',sans-serif;">
                            <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Group Label</label>
                            <input id="swalGroupLabel" class="swal2-input" placeholder="e.g. Cover Type" style="margin:0 0 15px;width:100%;box-sizing:border-box;">
                            
                            <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Group Type</label>
                            <select id="swalGroupType" class="swal2-input" style="margin:0;width:100%;box-sizing:border-box;">
                                <option value="Choice">Choice</option>
                                <option value="Page Tier">Page Tier</option>
                            </select>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonColor: '#4A7FB5',
                    confirmButtonText: 'Add Group',
                    preConfirm: () => {
                        const label = document.getElementById('swalGroupLabel').value.trim();
                        const type = document.getElementById('swalGroupType').value;
                        if (!label) {
                            Swal.showValidationMessage('Please enter a group label');
                            return false;
                        }
                        return { label, type };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        currentService.optionGroups.push({
                            id: 'g_' + Date.now(),
                            name: result.value.label,
                            type: result.value.type,
                            options: []
                        });
                        refreshServicesUI();
                        Swal.fire({ icon: 'success', title: 'Added Group!', timer: 1000, showConfirmButton: false });
                    }
                });
            }
        });
    }
    
    // Bind variant remove
    const btnRemoveVariants = bodyEl.querySelectorAll('.btn-remove-item[data-type="variant"]');
    btnRemoveVariants.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const itemId = e.currentTarget.getAttribute('data-id');
            if (!currentService) return;
            Swal.fire({
                title: 'Remove Variant?',
                text: 'Are you sure you want to delete this variant?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Yes, remove'
            }).then(result => {
                if (result.isConfirmed) {
                    currentService.variants = currentService.variants.filter(v => v.id != itemId);
                    refreshServicesUI();
                }
            });
        });
    });
    
    // Bind Option Group remove
    const btnRemoveGroups = bodyEl.querySelectorAll('.btn-remove-group');
    btnRemoveGroups.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const groupId = e.currentTarget.getAttribute('data-id');
            if (!currentService) return;
            Swal.fire({
                title: 'Remove Option Group?',
                text: 'This will delete the option group and all its items.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Yes, delete group'
            }).then(result => {
                if (result.isConfirmed) {
                    currentService.optionGroups = currentService.optionGroups.filter(g => g.id != groupId);
                    refreshServicesUI();
                }
            });
        });
    });
    
    // Bind "+ Add Item" inside option group card
    const btnAddOptionItems = bodyEl.querySelectorAll('.btn-add-option-item');
    btnAddOptionItems.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const groupId = e.currentTarget.getAttribute('data-group-id');
            if (!currentService) return;
            const group = currentService.optionGroups.find(g => g.id == groupId);
            if (!group) return;
            
            Swal.fire({
                title: `Add Item to "${group.name}"`,
                html: `
                    <div style="text-align:left; font-family:'Segoe UI',sans-serif;">
                        <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Item Name / Label</label>
                        <input id="swalOptName" class="swal2-input" placeholder="e.g. Clear Front" style="margin:0 0 15px;width:100%;box-sizing:border-box;">
                        
                        <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Add-on Price (₱)</label>
                        <input id="swalOptPrice" type="number" step="0.01" class="swal2-input" value="0.00" style="margin:0;width:100%;box-sizing:border-box;">
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonColor: '#4A7FB5',
                confirmButtonText: 'Add Item',
                preConfirm: () => {
                    const name = document.getElementById('swalOptName').value.trim();
                    const priceVal = document.getElementById('swalOptPrice').value.trim();
                    if (!name) {
                        Swal.showValidationMessage('Please enter a name');
                        return false;
                    }
                    const price = parseFloat(priceVal);
                    if (isNaN(price) || price < 0) {
                        Swal.showValidationMessage('Please enter a valid price');
                        return false;
                    }
                    return { name, price };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    group.options.push({
                        id: 'opt_' + Date.now(),
                        name: result.value.name,
                        price: result.value.price
                    });
                    refreshServicesUI();
                }
            });
        });
    });
    
    // Bind Option Item box click (to edit or remove option item)
    const optionBoxes = bodyEl.querySelectorAll('.option-item-box');
    optionBoxes.forEach(box => {
        box.addEventListener('click', (e) => {
            const groupId = e.currentTarget.getAttribute('data-group-id');
            const optionId = e.currentTarget.getAttribute('data-option-id');
            if (!currentService) return;
            const group = currentService.optionGroups.find(g => g.id == groupId);
            if (!group) return;
            const option = group.options.find(o => o.id == optionId);
            if (!option) return;
            
            Swal.fire({
                title: 'Manage Option Item',
                html: `
                    <div style="text-align:left; font-family:'Segoe UI',sans-serif;">
                        <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Item Name / Label</label>
                        <input id="swalEditOptName" class="swal2-input" value="${option.name}" style="margin:0 0 15px;width:100%;box-sizing:border-box;">
                        
                        <label style="font-weight:600;font-size:0.9rem;display:block;margin-bottom:5px;">Add-on Price (₱)</label>
                        <input id="swalEditOptPrice" type="number" step="0.01" class="swal2-input" value="${parseFloat(option.price || 0).toFixed(2)}" style="margin:0;width:100%;box-sizing:border-box;">
                    </div>
                `,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#4A7FB5',
                denyButtonColor: '#EF4444',
                confirmButtonText: 'Save',
                denyButtonText: 'Delete',
                preConfirm: () => {
                    const name = document.getElementById('swalEditOptName').value.trim();
                    const priceVal = document.getElementById('swalEditOptPrice').value.trim();
                    if (!name) {
                        Swal.showValidationMessage('Please enter a name');
                        return false;
                    }
                    const price = parseFloat(priceVal);
                    if (isNaN(price) || price < 0) {
                        Swal.showValidationMessage('Please enter a valid price');
                        return false;
                    }
                    return { name, price };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    option.name = result.value.name;
                    option.price = result.value.price;
                    refreshServicesUI();
                } else if (result.isDenied) {
                    group.options = group.options.filter(o => o.id != optionId);
                    refreshServicesUI();
                }
            });
        });
    });
    
    // Bind pricing preview simulation clicks
    if (currentAdminTab === 'pricing') {
        const simVariantBtns = bodyEl.querySelectorAll('.sim-btn-variant');
        simVariantBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const varId = e.currentTarget.getAttribute('data-id');
                window.pricingSimulationState.selectedVariantId = varId;
                updatePricingSimulationUI(bodyEl, currentService);
            });
        });
        
        const simOptionBtns = bodyEl.querySelectorAll('.sim-btn-option');
        simOptionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const grpId = e.currentTarget.getAttribute('data-group-id');
                const optId = e.currentTarget.getAttribute('data-option-id');
                window.pricingSimulationState.selectedOptionIds[grpId] = optId;
                updatePricingSimulationUI(bodyEl, currentService);
            });
        });
        
        // Compute initial simulation total
        updatePricingSimulationUI(bodyEl, currentService);
    }
}

function initAdmin() {
    initAdminServicesState();

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
        refreshServicesUI();
    } else if (target === 'transactions') {
        AdminView.renderTransactions(bodyEl);
    } else if (target === 'users') {
        AdminView.renderUsers(bodyEl);
    } else if (target === 'settings') {
        AdminView.renderSettings(bodyEl);
    } else if (target === 'reports') {
        headerEl.textContent = 'Reports';
        AdminView.renderReports(bodyEl);
    } else if (target === 'activity-log') {
        headerEl.textContent = 'Activity Log';
        AdminView.renderActivityLog(bodyEl);
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
</script>
