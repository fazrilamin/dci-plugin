<?php
/*
Plugin Name: DCI Plugin
Description: Visualize animated connection lines between data center nodes with admin panel and frontend shortcode.
Version: 1.2.1.4
Author: Fazril Amin
*/

if ( !defined( 'ABSPATH' ) ) exit;

function dci_enqueue_assets() {
    wp_enqueue_style('dci-style', plugin_dir_url(__FILE__) . 'assets/css/dci-style.css');
    wp_enqueue_script('dci-visualizer', plugin_dir_url(__FILE__) . 'assets/js/dci-visualizer.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'dci_enqueue_assets');

function dci_admin_assets($hook) {
    if ($hook != 'toplevel_page_dci-plugin') return;
    wp_enqueue_style('dci-admin-style', plugin_dir_url(__FILE__) . 'assets/css/dci-admin.css');
    wp_enqueue_script('dci-admin-js', plugin_dir_url(__FILE__) . 'assets/js/dci-admin.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'dci_admin_assets');

function dci_register_settings_page() {
    add_menu_page('DCI Plugin', 'DCI Plugin', 'manage_options', 'dci-plugin', 'dci_settings_page', 'dashicons-admin-generic', 80);
}
add_action('admin_menu', 'dci_register_settings_page');

function dci_register_settings() {
    register_setting('dci-settings-group', 'dci_nodes');
    register_setting('dci-settings-group', 'dci_connections');
}
add_action('admin_init', 'dci_register_settings');

// Add Settings link in Plugins page
function dci_plugin_action_links($links) {
    $settings_link = '<a href="admin.php?page=dci-plugin">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dci_plugin_action_links');

// Admin panel page content
function dci_settings_page() {
    $nodes = get_option('dci_nodes', []);
    $connections = get_option('dci_connections', []);
?>
<div class="wrap">
    <h1>DCI Plugin - Manage Nodes & Connections</h1>
    <form method="post" action="options.php">
        <?php settings_fields('dci-settings-group'); do_settings_sections('dci-settings-group'); ?>
        <div id="nodes-wrapper" class="card-section">
            <h3>Data Center Nodes <button type="button" id="add-node">Add Node</button></h3>
            <?php foreach ($nodes as $i => $node): ?>
                <div class="node-block">
                    Name: <input type="text" name="dci_nodes[<?php echo $i; ?>][name]" value="<?php echo esc_attr($node['name']); ?>" />
                    Location: <input type="text" name="dci_nodes[<?php echo $i; ?>][location]" value="<?php echo esc_attr($node['location']); ?>" />
                    Bandwidth: <input type="text" name="dci_nodes[<?php echo $i; ?>][bandwidth]" value="<?php echo esc_attr($node['bandwidth']); ?>" />
                    Top: <input type="number" name="dci_nodes[<?php echo $i; ?>][top]" value="<?php echo esc_attr($node['top']); ?>" />
                    Left: <input type="number" name="dci_nodes[<?php echo $i; ?>][left]" value="<?php echo esc_attr($node['left']); ?>" />
                    Size: <input type="number" name="dci_nodes[<?php echo $i; ?>][size]" value="<?php echo isset($node['size']) ? esc_attr($node['size']) : '30'; ?>" />
                    <button type="button" class="remove-node">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="connections-wrapper" class="card-section">
            <h3>Connections <button type="button" id="add-connection">Add Connection</button></h3>
            <?php foreach ($connections as $i => $conn): ?>
                <div class="conn-block">
                    From: 
                    <select name="dci_connections[<?php echo $i; ?>][from]" class="node-select">
                        <?php foreach ($nodes as $index => $node): ?>
                            <option value="<?php echo $index; ?>" <?php selected($conn['from'], $index); ?>>
                                <?php echo esc_html($node['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    To:
                    <select name="dci_connections[<?php echo $i; ?>][to]" class="node-select">
                        <?php foreach ($nodes as $index => $node): ?>
                            <option value="<?php echo $index; ?>" <?php selected($conn['to'], $index); ?>>
                                <?php echo esc_html($node['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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

// SHORTCODE also active
function dci_render_map() {
    $nodes = get_option('dci_nodes', []);
    $connections = get_option('dci_connections', []);
    ob_start(); ?>
    <div class="dci-map">
        <?php foreach($nodes as $i => $node): 
            $size = isset($node['size']) ? intval($node['size']) : 30; ?>
            <div class="dci-node pulse" style="top:<?php echo intval($node['top']); ?>px; left:<?php echo intval($node['left']); ?>px; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px; line-height: <?php echo $size; ?>px;">
                <span><?php echo esc_html($node['name']); ?><br><?php echo esc_html($node['bandwidth']); ?></span>
            </div>
        <?php endforeach; ?>
        <svg class="dci-lines">
            <?php foreach($connections as $conn): 
                $from = $nodes[$conn['from']];
                $to = $nodes[$conn['to']];
                $from_size = isset($from['size']) ? intval($from['size']) : 30;
                $to_size = isset($to['size']) ? intval($to['size']) : 30;
            ?>
                <line data-load="<?php echo esc_attr($conn['load']); ?>"
                      x1="<?php echo $from['left'] + $from_size/2; ?>"
                      y1="<?php echo $from['top'] + $from_size/2; ?>"
                      x2="<?php echo $to['left'] + $to_size/2; ?>"
                      y2="<?php echo $to['top'] + $to_size/2; ?>" />
            <?php endforeach; ?>
        </svg>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('dci_map', 'dci_render_map');
?>
