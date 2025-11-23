<?php
// Global app configuration and helpers for branding and currency
// Adjust these as needed

// Branding
if (!defined('PLATFORM_NAME')) {
    define('PLATFORM_NAME', 'Abdou store');
}

// Currency settings
// Legacy prices in DB are in Moroccan Dirham (MAD). We display in Tunisian Dinar (TND).
// Default conversion: 1 MAD -> 0.32 TND (approx). Change to match your business rate.
if (!defined('BASE_CURRENCY')) {
    define('BASE_CURRENCY', 'MAD');
}
if (!defined('DISPLAY_CURRENCY')) {
    define('DISPLAY_CURRENCY', 'TND');
}
if (!defined('MAD_TO_TND_RATE')) {
    define('MAD_TO_TND_RATE', 0.32);
}

/**
 * Convert a price from MAD (legacy) to TND (display)
 */
function convert_price_to_display($amountMad)
{
    if ($amountMad === null || $amountMad === '') return 0.0;
    $num = is_numeric($amountMad) ? (float)$amountMad : 0.0;
    return $num * MAD_TO_TND_RATE;
}

/**
 * Format a price (MAD -> TND) with symbol
 */
function format_price($amountMad)
{
    $tnd = convert_price_to_display($amountMad);
    return number_format($tnd, 2) . ' ' . DISPLAY_CURRENCY;
}

/**
 * Helper to echo the platform name safely
 */
function platform_name()
{
    return PLATFORM_NAME;
}
