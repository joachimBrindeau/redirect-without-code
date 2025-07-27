<?php
/**
 * Uninstall script for Redirect Without Code
 *
 * @package RedirectWithoutCode
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete database table
global $wpdb;
$table_name = $wpdb->prefix . 'sitemap_redirects';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete plugin options
delete_option('rwc_version');
delete_option('rwc_redirect_count');

// Clear any cached data
wp_cache_flush();
