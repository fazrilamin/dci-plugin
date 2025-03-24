<?php
/*
Plugin Name: DCI Plugin
Description: Visualize animated connection lines between data center nodes with dynamic admin form and live preview.
Version: 1.2.0
Author: Fazril Amin
*/

if ( !defined( 'ABSPATH' ) ) exit;

function dci_enqueue_assets() {
    wp_enqueue_style('dci-style', plugin_dir_url(__FILE__) . 'assets/css/dci-style.css');
    wp_enqueue_script('dci-visualizer', plugin_dir_url(__FILE__) . 'assets/js/dci-visualizer.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'dci_enqueue_assets');

// Admin assets
function dci_admin_assets($hook) {
    if ($hook != 'toplevel_page_dci-plugin') return;
    wp_enqueue_style('dci-admin-style', plugin_dir_url(__FILE__) . 'assets/css/dci-admin.css');
    wp_enqueue_script('dci-admin-js', plugin_dir_url(__FILE__) . 'assets/js/dci-admin.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'dci_admin_assets');

function dci_register_settings_page() {
    add_menu_page('DCI Plugin', 'DCI Plugin', 'manage_options', 'dci-plugin', 'dci_settings_page');
}
add_action('admin_menu', 'dci_register_settings_page');

function dci_register_settings() {
    register_setting('dci-settings-group', 'dci_nodes');
    register_setting('dci-settings-group', 'dci_connections');
}
add_action('admin_init', 'dci_register_settings');

function dci_settings_page() {
    $nodes = get_option('dci_nodes', []);
    $connections = get_option('dci_connections', []);
?>
<div class="wrap">
    <h1>DCI Plugin - Manage Nodes & Connections (Dynamic)</h1>
    <form method="post" action="options.php">
        <?php settings_fields('dci-settings-group'); do_settings_sections('dci-settings-group'); ?>
        <div id="nodes-wrapper">
            <h3>Data Center Nodes <button type="button" id="add-node">Add Node</button></h3>
            <?php foreach ($nodes as $i => $node): ?>
                <div class="node-block">
                    Name: <input type="text" name="dci_nodes[<?php echo $i; ?>][name]" value="<?php echo esc_attr($node['name']); ?>" />
                    Location: <input type="text" name="dci_nodes[<?php echo $i; ?>][location]" value="<?php echo esc_attr($node['location']); ?>" />
                    Bandwidth: <input type="text" name="dci_nodes[<?php echo $i; ?>][bandwidth]" value="<?php echo esc_attr($node['bandwidth']); ?>" />
                    Top: <input type="number" name="dci_nodes[<?php echo $i; ?>][top]" value="<?php echo esc_attr($node['top']); ?>" />
                    Left: <input type="number" name="dci_nodes[<?php echo $i; ?>][left]" value="<?php echo esc_attr($node['left']); ?>" />
                    <button type="button" class="remove-node">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="connections-wrapper">
            <h3>Connections <button type="button" id="add-connection">Add Connection</button></h3>
            <?php foreach ($connections as $i => $conn): ?>
                <div class="conn-block">
                    From: <input type="number" name="dci_connections[<?php echo $i; ?>][from]" value="<?php echo esc_attr($conn['from']); ?>" />
                    To: <input type="number" name="dci_connections[<?php echo $i; ?>][to]" value="<?php echo esc_attr($conn['to']); ?>" />
                    Load:
                    <select name="dci_connections[<?php echo $i; ?>][load]">
                        <option value="low" <?php selected($conn['load'], 'low'); ?>>Low</option>
                        <option value="medium" <?php selected($conn['load'], 'medium'); ?>>Medium</option>
                        <option value="high" <?php selected($conn['load'], 'high'); ?>>High</option>
                    </select>
                    <button type="button" class="remove-connection">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <?php submit_button(); ?>
    </form>

    <h3>Live Admin Preview</h3>
    <div id="dci-admin-preview">
        <svg id="dci-preview-lines"></svg>
        <div id="dci-preview-nodes"></div>
        <div class="grid-overlay"></div>
    </div>
</div>
<?php }

function dci_render_map() {
    $nodes = get_option('dci_nodes', []);
    $connections = get_option('dci_connections', []);
    ob_start(); ?>
    <div class="dci-map">
        <?php foreach($nodes as $i => $node): ?>
            <div class="dci-node" style="top:<?php echo $node['top']; ?>px; left:<?php echo $node['left']; ?>px;">
                <span><?php echo esc_html($node['name']); ?><br><?php echo esc_html($node['bandwidth']); ?></span>
            </div>
        <?php endforeach; ?>
        <svg class="dci-lines">
            <?php foreach($connections as $conn): ?>
                <line data-load="<?php echo esc_attr($conn['load']); ?>" x1="<?php echo $nodes[$conn['from']]['left'] + 25; ?>" y1="<?php echo $nodes[$conn['from']]['top'] + 25; ?>" x2="<?php echo $nodes[$conn['to']]['left'] + 25; ?>" y2="<?php echo $nodes[$conn['to']]['top'] + 25; ?>" />
            <?php endforeach; ?>
        </svg>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('dci_map', 'dci_render_map');
?>
