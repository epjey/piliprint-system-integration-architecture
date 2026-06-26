// ============================================================
//  ServiceModel.php
//  JavaScript served as a PHP file.
//  When using a PHP server, add header('Content-Type: application/javascript') above.
// ============================================================
// ============================================================
//  MODEL: ServiceModel.js
//  Holds all printing service definitions, variants, options
// ============================================================

const ServiceModel = (() => {

    const SHOP = {
        name: 'PILI PRINT SHOP',
        tagline: 'Professional Printing Services',
        address: '123 NCST COLLEGE., DASMA, PH',
        tel: 'Tel: (02) 8123-4567',
    };

    // SVG icon strings keyed by service id
    const ICONS = {
        bookbind: `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
      <rect x="10" y="8" width="30" height="42" rx="3" fill="#3d6294" opacity="0.15"/>
      <rect x="8"  y="8" width="6"  height="42" rx="2" fill="#3d6294"/>
      <rect x="14" y="14" width="22" height="2" rx="1" fill="#3d6294" opacity="0.5"/>
      <rect x="14" y="20" width="18" height="2" rx="1" fill="#3d6294" opacity="0.5"/>
      <rect x="14" y="26" width="20" height="2" rx="1" fill="#3d6294" opacity="0.5"/>
      <circle cx="38" cy="38" r="10" fill="#f5c518"/>
      <path d="M34 38 l3 3 l5-5" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
        printing: `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
      <rect x="10" y="22" width="40" height="22" rx="4" fill="#3d6294"/>
      <rect x="16" y="10" width="28" height="16" rx="2" fill="#b0becc"/>
      <rect x="16" y="36" width="28" height="16" rx="2" fill="#fff" stroke="#c8d4e3" stroke-width="1"/>
      <rect x="20" y="40" width="20" height="2" rx="1" fill="#aaa"/>
      <rect x="20" y="44" width="14" height="2" rx="1" fill="#aaa"/>
      <circle cx="40" cy="30" r="3" fill="#f5c518"/>
    </svg>`,
        photocopy: `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
      <rect x="8"  y="20" width="44" height="26" rx="4" fill="#3d6294"/>
      <rect x="14" y="8"  width="32" height="18" rx="2" fill="#b0becc"/>
      <rect x="14" y="36" width="32" height="14" rx="2" fill="#fff" stroke="#c8d4e3" stroke-width="1"/>
      <rect x="18" y="40" width="24" height="2" rx="1" fill="#aaa"/>
      <rect x="18" y="44" width="16" height="2" rx="1" fill="#aaa"/>
      <circle cx="42" cy="29" r="3" fill="#f5c518"/>
    </svg>`,
        lamination: `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
      <rect x="10" y="12" width="40" height="36" rx="3" fill="#b0becc" opacity="0.3"/>
      <rect x="10" y="12" width="40" height="36" rx="3" stroke="#3d6294" stroke-width="2.5" fill="none"/>
      <rect x="16" y="18" width="28" height="24" rx="2" fill="#fff" stroke="#c8d4e3" stroke-width="1"/>
      <text x="30" y="34" text-anchor="middle" font-size="8" font-weight="bold" fill="#3d6294" font-family="Arial">LAMINATE</text>
    </svg>`,
        rushid: `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
      <rect x="6" y="14" width="48" height="32" rx="4" fill="#d35400"/>
      <rect x="10" y="18" width="12" height="12" rx="2" fill="#fff" opacity="0.9"/>
      <rect x="26" y="20" width="22" height="2.5" rx="1" fill="#fff" opacity="0.8"/>
      <rect x="26" y="25" width="16" height="2.5" rx="1" fill="#fff" opacity="0.6"/>
      <rect x="26" y="30" width="18" height="2.5" rx="1" fill="#fff" opacity="0.6"/>
      <rect x="10" y="34" width="40" height="2" rx="1" fill="#fff" opacity="0.3"/>
      <rect x="10" y="38" width="30" height="2" rx="1" fill="#fff" opacity="0.3"/>
    </svg>`,
        pvc: `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
      <rect x="6" y="16" width="48" height="28" rx="4" fill="#ecf0f1" stroke="#c8d4e3" stroke-width="1.5"/>
      <rect x="6" y="16" width="48" height="9"  rx="4" fill="#3d6294"/>
      <rect x="6" y="21" width="48" height="4"  fill="#3d6294"/>
      <rect x="10" y="30" width="30" height="2.5" rx="1" fill="#aaa"/>
      <rect x="10" y="35" width="20" height="2" rx="1" fill="#ccc"/>
    </svg>`,
    };

    // Helper: placeholder icon for custom items
    const placeholderIcon = (label) => `<svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
    <rect x="10" y="8" width="40" height="44" rx="3" fill="#e9ecef" stroke="#c8d4e3" stroke-width="1.5"/>
    <text x="30" y="36" text-anchor="middle" font-size="8" fill="#999" font-family="Arial">${label || ''}</text>
  </svg>`;

    const services = [
        {
            id: 'bookbind',
            label: 'Book Bind',
            icon: ICONS.bookbind,
            variants: [
                { id: 'softbind', label: 'Soft Binding', price: 80 },
                { id: 'hardbind', label: 'Hard Binding', price: 150 },
                { id: 'spiralbind', label: 'Spiral Binding', price: 60 },
            ],
            optionGroups: [
                {
                    id: 'thickness',
                    label: 'Document Thickness',
                    options: [
                        { id: 't1', label: '1-50 pages', price: 0 },
                        { id: 't2', label: '51-100 pages', price: 20 },
                        { id: 't3', label: '101-200 pages', price: 40 },
                    ],
                },
                {
                    id: 'covertype',
                    label: 'Cover Type (spiral/comb binding)',
                    options: [
                        { id: 'c1', label: 'Clear Front + Cartolina Back', price: 0 },
                        { id: 'c2', label: 'Hard Cover', price: 50 },
                        { id: 'c3', label: 'Soft Cover', price: 20 },
                    ],
                },
                {
                    id: 'textcolor',
                    label: 'Text Color (thesis binding)',
                    options: [
                        { id: 'tc1', label: 'Gold', price: 0 },
                        { id: 'tc2', label: 'Silver', price: 0 },
                        { id: 'tc3', label: 'Black', price: 0 },
                    ],
                },
            ],
        },
        {
            id: 'printing',
            label: 'Printing',
            icon: ICONS.printing,
            variants: [
                { id: 'bond', label: 'Bond Paper', price: 1 },
                { id: 'glossy', label: 'Glossy Paper', price: 5 },
                { id: 'canvas', label: 'Canvas', price: 30 },
            ],
            optionGroups: [
                {
                    id: 'papersize',
                    label: 'Paper Size',
                    options: [
                        { id: 'ps1', label: 'Short', price: 0 },
                        { id: 'ps2', label: 'Long', price: 1 },
                        { id: 'ps3', label: 'A4', price: 0 },
                    ],
                },
                {
                    id: 'colormode',
                    label: 'Color Mode',
                    options: [
                        { id: 'cm1', label: 'Black & White', price: 0 },
                        { id: 'cm2', label: 'Colored', price: 5 },
                    ],
                },
            ],
        },
        {
            id: 'photocopy',
            label: 'Photocopy',
            icon: ICONS.photocopy,
            variants: [
                { id: 'pc_short', label: 'Short', price: 1 },
                { id: 'pc_long', label: 'Long', price: 1.5 },
                { id: 'pc_a4', label: 'A4', price: 1 },
            ],
            optionGroups: [
                {
                    id: 'colormode',
                    label: 'Color Mode',
                    options: [
                        { id: 'cm1', label: 'Black & White', price: 0 },
                        { id: 'cm2', label: 'Colored', price: 4 },
                    ],
                },
            ],
        },
        {
            id: 'lamination',
            label: 'Lamination',
            icon: ICONS.lamination,
            variants: [
                { id: 'lam_id', label: 'ID Size', price: 5 },
                { id: 'lam_half', label: 'Half Page', price: 10 },
                { id: 'lam_full', label: 'Full Page', price: 20 },
            ],
            optionGroups: [
                {
                    id: 'lamtype',
                    label: 'Lamination Type',
                    options: [
                        { id: 'lt1', label: 'Glossy', price: 0 },
                        { id: 'lt2', label: 'Matte', price: 5 },
                    ],
                },
            ],
        },
        {
            id: 'rushid',
            label: 'RusH id',
            icon: ICONS.rushid,
            variants: [
                { id: 'rid1', label: 'Standard ID', price: 50 },
                { id: 'rid2', label: 'Premium ID', price: 80 },
            ],
            optionGroups: [
                {
                    id: 'idsize',
                    label: 'ID Size',
                    options: [
                        { id: 'is1', label: 'CR80 (Standard)', price: 0 },
                        { id: 'is2', label: 'Custom Size', price: 10 },
                    ],
                },
            ],
        },
        {
            id: 'pvc',
            label: 'pvc',
            icon: ICONS.pvc,
            variants: [
                { id: 'pvc1', label: 'PVC Card', price: 30 },
            ],
            optionGroups: [],
        },
        {
            id: 'custom',
            label: 'werwrwrw',
            icon: placeholderIcon(''),
            variants: [
                { id: 'cust1', label: 'Custom Item', price: 10 },
            ],
            optionGroups: [],
        },
    ];

    return {
        getShopInfo: () => SHOP,
        getAll: () => services,
        getById: (id) => services.find(s => s.id === id),
    };
})();
