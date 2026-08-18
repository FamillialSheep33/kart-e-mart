<?php
if (defined('SITE_NAME')) return;

//i use this file to set up anything else on a quick way,
//like fonts, strings and passwords

//this was made for the modularity, i can easily sell the page to another
//customer with just changing the strings and logos, and colors


// ── 1. IDENTIDAD ──────────────────────────────────────────
define('SITE_NAME',        'Refaccionaria patito');
define('SITE_TAGLINE',     'Refacciones al mejor precio');
define('SITE_DESCRIPTION', 'Indiscutiblemente los mejores de Tulancingo.');
define('SITE_YEAR',        '2026');
define('SITE_LANG',        'es');

//theme and colors
define('DEFAULT_THEME', 'dark');

//dark theme
define('DARK_PRIMARY',      '#ff8c33');
define('DARK_BG',           '#0b0d11');
define('DARK_SURFACE',      '#161920');
define('DARK_SURFACE2',     '#1e222d');
define('DARK_TEXT',         '#f8fafc');
define('DARK_TEXT_MUTED',   '#94a3b8');
define('DARK_BORDER',       '#2d3343');
define('DARK_ACCENT',       '#ffb74d');

//clear theme
define('LIGHT_PRIMARY',      '#f47920');
define('LIGHT_PRIMARY_DARK', '#d84315');
define('LIGHT_BG',           '#f9fafb');
define('LIGHT_SURFACE',      '#ffffff');
define('LIGHT_SURFACE2',     '#f3f4f6');
define('LIGHT_TEXT',         '#22262f');
define('LIGHT_TEXT_MUTED',   '#6b7280');
define('LIGHT_BORDER',       '#e5e7eb');
define('LIGHT_ACCENT',       '#e64a19');


//  fonts and stuff
define('FONT_DISPLAY', 'Syne');
define('FONT_BODY',    'DM Sans');
define('FONT_IMPORT',  'https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap');


// strings for the webpage
define('BANNER_TITLE',      'REFACCIONEs');
define('BANNER_SUBTITLE',   'Llantas · Juntas · Transmisiones · Reemplazos');
define('FREE_SHIPPING_TEXT','Envío gratis en todos los pedidos');
define('CATALOG_SECTION',   'Todos los productos');
define('CURRENCY_SYMBOL',   '$');


//database credencials
define('DB_HOST', 'localhost');
define('DB_USER', 'private');       //user for the database
define('DB_PASS', 'private');       // password for the database
define('DB_NAME', 'private');       // name of the database to use




// here we define where the images of the products will go, and the placeholder if some product lose
//their images
define('UPLOADS_DIR',    'uploads/');
define('PLACEHOLDER_IMG','https://placehold.co/400x400/1e2230/7a8099?text=Sin+imagen');

//      links for the navbar
define('NAV_TRACKING_LABEL', 'Rastrear pedido');
define('NAV_TRACKING_URL',   'tracking.html');

//footer
define('FOOTER_TEXT', '&copy; ' . SITE_YEAR . ' ' . SITE_NAME . ' &mdash; ' . SITE_TAGLINE);

// ── 13. FEATURES ──────────────────────────────────────────
define('FEATURE_SEARCH',          true);
define('FEATURE_TRACKING',        true);
define('FEATURE_FREE_SHIP_BADGE', true);
define('FEATURE_THEME_TOGGLE',    true);


function themeColors(): array {
    $theme = $_COOKIE['theme'] ?? DEFAULT_THEME;
    if (!in_array($theme, ['dark','light'])) $theme = DEFAULT_THEME;
    return $theme === 'dark' ? [
        'primary'      => DARK_PRIMARY,
        'primary_dark' => DARK_PRIMARY_DARK,
        'bg'           => DARK_BG,
        'surface'      => DARK_SURFACE,
        'surface2'     => DARK_SURFACE2,
        'text'         => DARK_TEXT,
        'muted'        => DARK_TEXT_MUTED,
        'border'       => DARK_BORDER,
        'accent'       => DARK_ACCENT,
        'name'         => 'dark',
    ] : [
        'primary'      => LIGHT_PRIMARY,
        'primary_dark' => LIGHT_PRIMARY_DARK,
        'bg'           => LIGHT_BG,
        'surface'      => LIGHT_SURFACE,
        'surface2'     => LIGHT_SURFACE2,
        'text'         => LIGHT_TEXT,
        'muted'        => LIGHT_TEXT_MUTED,
        'border'       => LIGHT_BORDER,
        'accent'       => LIGHT_ACCENT,
        'name'         => 'light',
    ];
}

/**
 * Hex → rgba() — compatible con todos los navegadores.
 * Sustituye color-mix() que requiere Firefox 113+.
 */
function rgba(string $hex, float $alpha = 1.0): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));
    return "rgba($r,$g,$b,$alpha)";
}

/** Precio formateado correctamente */
function formatPrice(float $v): string {
    return number_format($v, 2, '.', ',');
}
