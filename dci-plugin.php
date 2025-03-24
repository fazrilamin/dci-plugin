<?php
/*
Plugin Name: DCI Plugin
Description: Visualize animated connection lines between static data center nodes.
Version: 1.0.0
Author: Fazril Amin
*/

if ( !defined( 'ABSPATH' ) ) exit;

// Enqueue scripts and styles
function dci_enqueue_assets() {
    wp_enqueue_style('dci-style', plugin_dir_url(__FILE__) . 'assets/css/dci-style.css');
    wp_enqueue_script('dci-visualizer', plugin_dir_url(__FILE__) . 'assets/js/dci-visualizer.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'dci_enqueue_assets');

// Shortcode to render map
function dci_render_map() {
    ob_start(); ?>
    <div class="dci-map">
        <div class="dci-node node-1"></div>
        <div class="dci-node node-2"></div>
        <div class="dci-node node-3"></div>
        <svg class="dci-lines">
            <line x1="100" y1="100" x2="300" y2="100" />
            <line x1="300" y1="100" x2="200" y2="250" />
            <line x1="200" y1="250" x2="100" y2="100" />
        </svg>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('dci_map', 'dci_render_map');
?>
