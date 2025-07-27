<?php
/**
 * Admin page template
 *
 * @package RedirectWithoutCode
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Redirect Without Code', 'redirect-without-code'); ?></h1>
    
    <div class="sri-card">
        <h2><?php _e('Add Single Redirect', 'redirect-without-code'); ?></h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="add_redirect">
            <?php wp_nonce_field('add_redirect_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="old_path"><?php _e('Old Path', 'redirect-without-code'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="old_path" name="old_path" placeholder="/old-page" class="regular-text" required>
                        <p class="description"><?php _e('The old URL path that should redirect (e.g., /old-page)', 'redirect-without-code'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="new_path"><?php _e('New Path', 'redirect-without-code'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="new_path" name="new_path" placeholder="/new-page" class="regular-text" required>
                        <p class="description"><?php _e('The new URL path to redirect to (e.g., /new-page)', 'redirect-without-code'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Add Redirect', 'redirect-without-code'), 'primary', 'submit', false); ?>
        </form>
    </div>
    
    <div class="sri-card">
        <h2><?php _e('Import CSV File', 'redirect-without-code'); ?></h2>
        <p><?php _e('Upload your CSV file. Must contain columns: path_old, path_new, status. Only rows with status "301" will be imported.', 'redirect-without-code'); ?></p>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import_redirects">
            <?php wp_nonce_field('import_redirects_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="csv_file"><?php _e('CSV File', 'redirect-without-code'); ?></label>
                    </th>
                    <td>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        <p class="description">
                            <?php _e('Required columns: path_old, path_new, status', 'redirect-without-code'); ?><br>
                            <?php _e('Examples:', 'redirect-without-code'); ?><br>
                            <code>path_old,path_new,status</code><br>
                            <code>domain_old,domain_new,path_old,path_new,status</code>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Options', 'redirect-without-code'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="clear_existing" value="1">
                                <?php _e('Clear existing redirects before import', 'redirect-without-code'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="skip_duplicates" value="1" checked>
                                <?php _e('Skip duplicate old paths', 'redirect-without-code'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Import Redirects', 'redirect-without-code')); ?>
        </form>
    </div>
    
    <div class="sri-card">
        <h2>
            <?php 
            printf(
                __('Current Redirects (%d/%d active)', 'redirect-without-code'),
                $active_redirects,
                $total_redirects
            ); 
            ?>
        </h2>
        
        <?php if (empty($redirects)): ?>
            <p><?php _e('No redirects found. Import a CSV file or add redirects manually to get started.', 'redirect-without-code'); ?></p>
        <?php else: ?>
            <div class="tablenav top">
                <div class="alignleft actions">
                    <span class="displaying-num">
                        <?php printf(_n('%s item', '%s items', $total_redirects, 'redirect-without-code'), number_format_i18n($total_redirects)); ?>
                    </span>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column"><?php _e('Old Path', 'redirect-without-code'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('New Path', 'redirect-without-code'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Status', 'redirect-without-code'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Created', 'redirect-without-code'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Actions', 'redirect-without-code'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($redirects as $redirect): ?>
                        <tr <?php echo !$redirect->active ? 'class="inactive"' : ''; ?>>
                            <td><code><?php echo esc_html($redirect->old_path); ?></code></td>
                            <td><code><?php echo esc_html($redirect->new_path); ?></code></td>
                            <td>
                                <span class="status-<?php echo $redirect->active ? 'active' : 'inactive'; ?>">
                                    <?php echo $redirect->active ? __('Active', 'redirect-without-code') : __('Inactive', 'redirect-without-code'); ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($redirect->created_at)); ?></td>
                            <td>
                                <a href="<?php echo wp_nonce_url(
                                    admin_url('tools.php?page=redirect-without-code&action=toggle&id=' . $redirect->id),
                                    'toggle_redirect'
                                ); ?>" class="button button-small">
                                    <?php echo $redirect->active ? __('Disable', 'redirect-without-code') : __('Enable', 'redirect-without-code'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(
                                    admin_url('tools.php?page=redirect-without-code&action=delete&id=' . $redirect->id),
                                    'delete_redirect'
                                ); ?>" 
                                   class="button button-small button-link-delete"
                                   onclick="return confirm('<?php _e('Are you sure you want to delete this redirect?', 'redirect-without-code'); ?>')">
                                    <?php _e('Delete', 'redirect-without-code'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
