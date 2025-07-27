<?php
/**
 * Plugin Name: Redirect Without Code
 * Plugin URI: https://github.com/joachimBrindeau/redirect-without-code
 * Description: Import CSV files and create 301 redirects without writing any code. Perfect for website migrations and URL structure changes.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Joachim Brindeau
 * Author URI: https://github.com/joachimBrindeau
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: redirect-without-code
 * Domain Path: /languages
 * Network: false
 *
 * @package RedirectWithoutCode
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RWC_VERSION', '1.0.0');
define('RWC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RWC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RWC_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class RedirectWithoutCode {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('RedirectWithoutCode', 'uninstall'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('redirect-without-code', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Admin functionality
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_post_import_redirects', array($this, 'handle_import'));
            add_action('admin_post_add_redirect', array($this, 'handle_add_redirect'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_action('wp_ajax_rwc_update_redirect', array($this, 'handle_update_redirect'));
        }
        
        // Frontend redirect handling
        add_action('template_redirect', array($this, 'handle_redirects'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_redirects_table();
        
        // Set default options
        add_option('rwc_version', RWC_VERSION);
        add_option('rwc_redirect_count', 0);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        global $wpdb;
        
        // Remove database table
        $table_name = $wpdb->prefix. 'sitemap_redirects';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        // Remove options
        delete_option('rwc_version');
        delete_option('rwc_redirect_count');
    }
    
    /**
     * Create redirects database table
     */
    public function create_redirects_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix. 'sitemap_redirects';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            old_path varchar(500) NOT NULL,
            new_path varchar(500) NOT NULL,
            status varchar(20) DEFAULT '301',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY old_path (old_path),
            KEY active (active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_management_page(
            __('Redirect Without Code', 'redirect-without-code'),
            __('Redirect Without Code', 'redirect-without-code'),
            'manage_options',
            'redirect-without-code',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts($hook) {
        if ('tools_page_redirect-without-code' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'rwc-admin-style',
            RWC_PLUGIN_URL . 'assets/admin.css',
            array(),
            RWC_VERSION
        );

        // Enqueue jQuery and localize ajaxurl
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'rwc_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rwc_update_redirect')
        ));
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        // Security check
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'redirect-without-code'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'sitemap_redirects';
        
        // Handle actions
        $this->handle_admin_actions($table_name);
        
        // Get redirects data
        $redirects = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        $total_redirects = count($redirects);
        $active_redirects = count(array_filter($redirects, function($r) { return $r->active; }));
        
        // Include admin template
        include RWC_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Handle admin actions (delete, toggle)
     */
    private function handle_admin_actions($table_name) {
        global $wpdb;
        
        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_redirect')) {
            $deleted = $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
            if ($deleted) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Redirect deleted successfully!', 'redirect-without-code') . '</p></div>';
                });
            }
        }
        
        // Handle toggle action
        if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'toggle_redirect')) {
            $current = $wpdb->get_var($wpdb->prepare("SELECT active FROM $table_name WHERE id = %d", intval($_GET['id'])));
            $new_status = $current ? 0 : 1;
            $updated = $wpdb->update($table_name, array('active' => $new_status), array('id' => intval($_GET['id'])));
            if ($updated !== false) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Redirect status updated!', 'redirect-without-code') . '</p></div>';
                });
            }
        }
        
        // Handle success/error messages
        $this->handle_admin_messages();
    }
    
    /**
     * Handle admin messages
     */
    private function handle_admin_messages() {
        if (isset($_GET['added'])) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Redirect added successfully!', 'redirect-without-code') . '</p></div>';
            });
        }
        
        if (isset($_GET['imported']) && isset($_GET['skipped'])) {
            $imported = intval($_GET['imported']);
            $skipped = intval($_GET['skipped']);
            add_action('admin_notices', function() use ($imported, $skipped) {
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                     sprintf(__('Import completed! Imported: %d redirects, Skipped: %d rows', 'redirect-without-code'), $imported, $skipped) . 
                     '</p></div>';
            });
        }
        
        if (isset($_GET['error'])) {
            $error_messages = array(
                'upload' => __('File upload failed.', 'redirect-without-code'),
                'read' => __('Could not read the uploaded file.', 'redirect-without-code'),
                'empty' => __('Both old path and new path are required.', 'redirect-without-code'),
                'same' => __('Old path and new path cannot be the same.', 'redirect-without-code'),
                'duplicate' => __('A redirect for this old path already exists.', 'redirect-without-code'),
                'database' => __('Database error occurred while adding redirect.', 'redirect-without-code')
            );
            
            $error = sanitize_key($_GET['error']);
            if (isset($error_messages[$error])) {
                add_action('admin_notices', function() use ($error_messages, $error) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . 
                         __('Error: ', 'redirect-without-code') . $error_messages[$error] . 
                         '</p></div>';
                });
            }
        }
    }
    
    /**
     * Handle CSV import
     */
    public function handle_import() {
        // Security checks
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'import_redirects_nonce')) {
            wp_die(__('Unauthorized access.', 'redirect-without-code'));
        }
        
        // Validate file upload
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(admin_url('tools.php?page=redirect-without-code&error=upload'));
            exit;
        }
        
        // Validate file type
        $file_info = pathinfo($_FILES['csv_file']['name']);
        if (!isset($file_info['extension']) || strtolower($file_info['extension']) !== 'csv') {
            wp_redirect(admin_url('tools.php?page=redirect-without-code&error=filetype'));
            exit;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix. 'sitemap_redirects';
        
        // Clear existing if requested
        if (isset($_POST['clear_existing'])) {
            $wpdb->query("TRUNCATE TABLE $table_name");
        }
        
        // Process CSV file
        $result = $this->process_csv_file($_FILES['csv_file']['tmp_name'], $table_name);
        
        wp_redirect(admin_url("tools.php?page=redirect-without-code&imported={$result['imported']}&skipped={$result['skipped']}"));
        exit;
    }

    /**
     * Process CSV file
     */
    private function process_csv_file($file_path, $table_name) {
        global $wpdb;

        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return array('imported' => 0, 'skipped' => 0);
        }

        // Read header row to find column positions
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return array('imported' => 0, 'skipped' => 0);
        }

        // Find required column positions (case-insensitive)
        $header = array_map('strtolower', array_map('trim', $header));
        $path_old_col = array_search('path_old', $header);
        $path_new_col = array_search('path_new', $header);
        $status_col = array_search('status', $header);

        // Check if all required columns exist
        if ($path_old_col === false || $path_new_col === false || $status_col === false) {
            fclose($handle);
            return array('imported' => 0, 'skipped' => 0);
        }

        $imported = 0;
        $skipped = 0;
        $skip_duplicates = isset($_POST['skip_duplicates']);

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Extract required columns by position
            $path_old = isset($data[$path_old_col]) ? trim($data[$path_old_col]) : '';
            $path_new = isset($data[$path_new_col]) ? trim($data[$path_new_col]) : '';
            $status = isset($data[$status_col]) ? trim($data[$status_col]) : '';

            // Only process 301 redirects
            if ($status !== '301' || empty($path_old) || empty($path_new)) {
                $skipped++;
                continue;
            }

            // Sanitize and format paths
            $path_old = $this->sanitize_path($path_old);
            $path_new = $this->sanitize_path($path_new);

            // Skip duplicates if requested
            if ($skip_duplicates) {
                $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE old_path = %s", $path_old));
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }

            // Insert redirect
            $result = $wpdb->insert(
                $table_name,
                array(
                    'old_path' => $path_old,
                    'new_path' => $path_new,
                    'status' => '301',
                    'active' => 1
                ),
                array('%s', '%s', '%s', '%d')
            );

            if ($result) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        fclose($handle);

        return array('imported' => $imported, 'skipped' => $skipped);
    }

    /**
     * Handle manual redirect addition
     */
    public function handle_add_redirect() {
        // Security checks
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'add_redirect_nonce')) {
            wp_die(__('Unauthorized access.', 'redirect-without-code'));
        }

        $old_path = sanitize_text_field($_POST['old_path']);
        $new_path = sanitize_text_field($_POST['new_path']);

        // Sanitize and format paths
        $old_path = $this->sanitize_path($old_path);
        $new_path = $this->sanitize_path($new_path);

        // Validate inputs
        if (empty($old_path) || empty($new_path)) {
            wp_redirect(admin_url('tools.php?page=redirect-without-code&error=empty'));
            exit;
        }

        if ($old_path === $new_path) {
            wp_redirect(admin_url('tools.php?page=redirect-without-code&error=same'));
            exit;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'sitemap_redirects';

        // Check for duplicates
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE old_path = %s", $old_path));
        if ($exists) {
            wp_redirect(admin_url('tools.php?page=redirect-without-code&error=duplicate'));
            exit;
        }

        // Insert redirect
        $result = $wpdb->insert(
            $table_name,
            array(
                'old_path' => $old_path,
                'new_path' => $new_path,
                'status' => '301',
                'active' => 1
            ),
            array('%s', '%s', '%s', '%d')
        );

        if ($result) {
            wp_redirect(admin_url('tools.php?page=redirect-without-code&added=1'));
        } else {
            wp_redirect(admin_url('tools.php?page=redirect-without-code&error=database'));
        }
        exit;
    }

    /**
     * Handle AJAX redirect update
     */
    public function handle_update_redirect() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(__('Invalid request method.', 'redirect-without-code'));
            return;
        }

        // Security checks
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'rwc_update_redirect')) {
            wp_send_json_error(__('Unauthorized access.', 'redirect-without-code'));
            return;
        }

        $id = intval($_POST['id']);
        $old_path = sanitize_text_field($_POST['old_path']);
        $new_path = sanitize_text_field($_POST['new_path']);

        // Sanitize and format paths
        $old_path = $this->sanitize_path($old_path);
        $new_path = $this->sanitize_path($new_path);

        // Validate inputs
        if (empty($old_path) || empty($new_path)) {
            wp_send_json_error(__('Both old path and new path are required.', 'redirect-without-code'));
            return;
        }

        if ($old_path === $new_path) {
            wp_send_json_error(__('Old path and new path cannot be the same.', 'redirect-without-code'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'sitemap_redirects';

        // Check for duplicates (excluding current record)
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE old_path = %s AND id != %d",
            $old_path,
            $id
        ));

        if ($exists) {
            wp_send_json_error(__('A redirect for this old path already exists.', 'redirect-without-code'));
            return;
        }

        // Update redirect
        $result = $wpdb->update(
            $table_name,
            array(
                'old_path' => $old_path,
                'new_path' => $new_path
            ),
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(__('Redirect updated successfully.', 'redirect-without-code'));
        } else {
            wp_send_json_error(__('Database error occurred while updating redirect.', 'redirect-without-code'));
        }
    }

    /**
     * Sanitize and format URL path
     */
    private function sanitize_path($path) {
        $path = trim($path);

        // Ensure path starts with /
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }

        // Remove trailing slash except for root
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        return $path;
    }

    /**
     * Handle frontend redirects
     */
    public function handle_redirects() {
        // Skip admin pages
        if (is_admin()) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'sitemap_redirects';

        // Get current path
        $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $current_path = $this->sanitize_path($current_path);

        // Look for redirect
        $redirect = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE old_path = %s AND active = 1 LIMIT 1",
            $current_path
        ));

        if ($redirect) {
            $new_url = home_url($redirect->new_path);
            wp_redirect($new_url, 301);
            exit;
        }
    }
}

// Initialize plugin
RedirectWithoutCode::get_instance();