<?php
/**
 * KINDORA - GLOBAL PATHS CONFIGURATION
 * Location: /Kindora/paths.php
 * 
 * This file manages all paths for images, CSS, JS, pages, etc.
 * Works from ANY folder!
 */

// ============================================
// BASE PATHS
// ============================================

$basePath = 'http://localhost/Kindora/';
$assetsPath = $basePath . 'assets/';

// ============================================
// PATH REGISTRY (Array of all paths)
// ============================================

$path = array(
    // ============================================
    // CSS PATHS
    // ============================================
    'css_homepage' => $assetsPath . 'css/homepage.css',
    'css_common' => $assetsPath . 'css/common_nav_footer.css',
    'css_7wonders' => $assetsPath . 'css/7wonders.css',
    'css_pages_asia' => $assetsPath . 'css/pages/asia.css',
    'css_booking' => $assetsPath . 'css/pages/booking.css',
    'css_explore' => $assetsPath . 'css/pages/explore.css',
    
    // ============================================
    // JAVASCRIPT PATHS
    // ============================================
    'js_common' => $assetsPath . 'js/common_nav_footer.js',
    'js_users' => $assetsPath . 'js/users.js',
    'js_data' => $assetsPath . 'js/data.js',
    
    // ============================================
    // VIDEO PATHS
    // ============================================
    'video_bg' => $basePath . 'bgvideo.mp4',
    
    // ============================================
    // ICON PATHS
    // ============================================
    'icon_facebook' => $basePath . 'icons/facebook.jpeg',
    'icon_instagram' => $basePath . 'icons/instagram.jpeg',
    'icon_twitter' => $basePath . 'icons/twitter.avif',
    
    // ============================================
    // BANNER PATHS
    // ============================================
    'banner_asia' => $basePath . 'web images/banner/asia.avif',
    'banner_europe' => $basePath . 'web images/banner/europe.jpeg',
    'banner_africa' => $basePath . 'web images/banner/africa.avif',
    'banner_north_america' => $basePath . 'web images/banner/north america.avif',
    'banner_south_america' => $basePath . '7wonders/7_wonders/Machu Picchu.jpeg',
    'banner_australia' => $basePath . 'web images/banner/australia.avif',
    'banner_antarctica' => $basePath . 'web images/banner/antarctica.jpeg',
    
    // ============================================
    // IMAGE FOLDER PATHS
    // ============================================
    'images_places' => $basePath . 'places/',
    'images_packages' => $basePath . 'our_packages/',
    'images_deals' => $basePath . 'images/',
    'images_7wonders' => $basePath . '7wonders/7_wonders/',
    'images_web' => $basePath . 'web images/',
    'images_icons' => $basePath . 'icons/',
);

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get a path by name
 * Usage: echo getPath('css_homepage');
 * Usage: echo getPath('video_bg');
 */
function getPath($pathName) {
    global $path;
    return isset($path[$pathName]) ? $path[$pathName] : '';
}

/**
 * Get image path by category and filename
 * Usage: echo getImagePath('places', 'Eiffel Tower.avif');
 * Usage: echo getImagePath('7wonders', 'Taj-Mahal.avif');
 */
function getImagePath($category, $filename) {
    global $basePath;
    
    $categoryMap = array(
        'places' => $basePath . 'places/',
        'packages' => $basePath . 'our_packages/',
        'images' => $basePath . 'images/',
        'deals' => $basePath . 'images/',
        '7wonders' => $basePath . '7wonders/7_wonders/',
        'banners' => $basePath . 'web images/banner/',
    );
    
    $categoryPath = isset($categoryMap[$category]) ? $categoryMap[$category] : ($basePath . $category . '/');
    return $categoryPath . $filename;
}

/**
 * Generate CSS link tag
 * Usage: echo linkCSS('homepage');
 */
function linkCSS($cssName) {
    global $path, $assetsPath;
    $pathKey = 'css_' . str_replace('-', '_', $cssName);
    $cssPath = isset($path[$pathKey]) ? $path[$pathKey] : ($assetsPath . 'css/' . $cssName . '.css');
    return '<link href="' . htmlspecialchars($cssPath) . '" rel="stylesheet" />' . "\n";
}

/**
 * Generate JavaScript script tag
 * Usage: echo linkJS('common');
 */
function linkJS($jsName) {
    global $path, $assetsPath;
    $pathKey = 'js_' . str_replace('-', '_', $jsName);
    $jsPath = isset($path[$pathKey]) ? $path[$pathKey] : ($assetsPath . 'js/' . $jsName . '.js');
    return '<script src="' . htmlspecialchars($jsPath) . '"></script>' . "\n";
}

/**
 * Get full URL base
 * Usage: echo getBase();
 */
function getBase() {
    global $basePath;
    return $basePath;
}

?>
