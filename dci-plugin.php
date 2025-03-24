<?php
/*
Plugin Name: DCI Plugin
Description: Visualize animated connection lines between static data center nodes with admin settings.
Version: 1.1.0
Author: Fazril Amin
*/

if ( !defined( 'ABSPATH' ) ) exit;

// Enqueue scripts and styles
function dci_enqueue_assets() {
    wp_enqueue_style('dci-style', plugin_dir_url(__FILE__) . 'assets/css/dci-style.css');
    wp_enqueue_script('dci-visualizer', plugin_dir_url(__FILE__) . 'assets/js/dci-visualizer.js', array('jquery'), null, true);
    wp_localize_script('dci-visualizer', 'dciData', array(
        'pulse' => get_option('dci_pulse_effect', 'yes')
    ));
}
add_action('wp_enqueue_scripts', 'dci_enqueue_assets');

// Admin menu
function dci_register_settings_page() {
    add_options_page('DCI Plugin Settings', 'DCI Plugin', 'manage_options', 'dci-plugin', 'dci_settings_page');
}
add_action('admin_menu', 'dci_register_settings_page');

// Register settings
function dci_register_settings() {
    register_setting('dci-settings-group', 'dci_pulse_effect');
    register_setting('dci-settings-group', 'dci_map_title');
}
add_action('admin_init', 'dci_register_settings');

// Settings page content
function dci_settings_page() { ?>
    <div class="wrap">
        <h1>DCI Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('dci-settings-group'); do_settings_sections('dci-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Map Title</th>
                    <td><input type="text" name="dci_map_title" value="<?php echo esc_attr(get_option('dci_map_title')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Pulse Effect?</th>
                    <td>
                        <select name="dci_pulse_effect">
                            <option value="yes" <?php selected(get_option('dci_pulse_effect'), 'yes'); ?>>Yes</option>
                            <option value="no" <?php selected(get_option('dci_pulse_effect'), 'no'); ?>>No</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php }

// Shortcode to render map
function dci_render_map() {
    $map_title = esc_html(get_option('dci_map_title', 'Data Center Map'));
    $nodes = [
        ['top' => '90px', 'left' => '90px'],
        ['top' => '90px', 'left' => '290px'],
        ['top' => '240px', 'left' => '190px'],
    ];
    ob_start(); ?>
    <div class="dci-map">
        <h2 style="color: white; text-align:center;"><?php echo $map_title; ?></h2>
        <?php foreach($nodes as $i => $node): ?>
            <div class="dci-node node-<?php echo $i+1; ?>" style="top:<?php echo $node['top']; ?>; left:<?php echo $node['left']; ?>;"></div>
        <?php endforeach; ?>
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
