<?php
// ============================================
// Roumayssa Shop - Configuration
// ============================================

// Site Information
define('SITE_NAME', 'Roumayssa Shop');
define('SITE_TAGLINE', 'Votre boutique pour femmes et bébés');
define('SITE_URL', 'http://localhost:8000');

// Contact Information
define('WHATSAPP_NUMBER', '+216XXXXXXXX'); // À remplacer par votre numéro
define('CONTACT_EMAIL', 'contact@roumayssa-shop.com');
define('CONTACT_PHONE', '+216 XX XXX XXX');

// Social Media
define('FACEBOOK_URL', 'https://facebook.com/roumayssa-shop');
define('INSTAGRAM_URL', 'https://instagram.com/roumayssa_shop');

// SEO Defaults
define('DEFAULT_META_DESCRIPTION', 'Roumayssa Shop - Votre boutique en ligne de vêtements pour femmes, accessoires et produits pour bébé en Tunisie.');
define('DEFAULT_META_KEYWORDS', 'vêtements femme, accessoires, produits bébé, boutique en ligne, Tunisie');

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);

// Upload Settings
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5 MB

// Currency
define('CURRENCY', 'DT');
define('CURRENCY_POSITION', 'after'); // 'before' or 'after'

// Delivery Options
$deliveryOptions = [
    'home' => 'Livraison à domicile (7 DT)',
    'pickup' => 'Retrait en magasin (Gratuit)'
];

// Order Status
$orderStatus = [
    'new' => 'Nouvelle',
    'processing' => 'En cours',
    'shipped' => 'Expédiée',
    'delivered' => 'Livrée',
    'cancelled' => 'Annulée'
];
?>
