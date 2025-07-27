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
                        <tr <?php echo !$redirect->active ? 'class="inactive"' : ''; ?> data-id="<?php echo $redirect->id; ?>">
                            <td class="editable-cell" data-field="old_path">
                                <span class="display-value"><code><?php echo esc_html($redirect->old_path); ?></code></span>
                                <input type="text" class="edit-input" value="<?php echo esc_attr($redirect->old_path); ?>" style="display:none;">
                            </td>
                            <td class="editable-cell" data-field="new_path">
                                <span class="display-value"><code><?php echo esc_html($redirect->new_path); ?></code></span>
                                <input type="text" class="edit-input" value="<?php echo esc_attr($redirect->new_path); ?>" style="display:none;">
                            </td>
                            <td>
                                <span class="status-<?php echo $redirect->active ? 'active' : 'inactive'; ?>">
                                    <?php echo $redirect->active ? __('Active', 'redirect-without-code') : __('Inactive', 'redirect-without-code'); ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($redirect->created_at)); ?></td>
                            <td class="actions-cell">
                                <div class="row-actions-wrapper">
                                    <div class="normal-actions">
                                        <button class="button button-small edit-btn" title="<?php _e('Edit', 'redirect-without-code'); ?>">
                                            <?php _e('Edit', 'redirect-without-code'); ?>
                                        </button>
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
                                    </div>
                                    <div class="edit-actions" style="display:none;">
                                        <button class="button button-small button-primary save-btn" title="<?php _e('Save', 'redirect-without-code'); ?>">
                                            <?php _e('Save', 'redirect-without-code'); ?>
                                        </button>
                                        <button class="button button-small cancel-btn" title="<?php _e('Cancel', 'redirect-without-code'); ?>">
                                            <?php _e('Cancel', 'redirect-without-code'); ?>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Use event delegation for dynamic content
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        enterEditMode(row);
    });

    $(document).on('click', '.save-btn', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        saveRow(row);
    });

    $(document).on('click', '.cancel-btn', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        cancelEdit(row);
    });

    $(document).on('keypress', '.edit-input', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            var row = $(this).closest('tr');
            saveRow(row);
        }
    });

    $(document).on('keyup', '.edit-input', function(e) {
        if (e.which === 27) {
            var row = $(this).closest('tr');
            cancelEdit(row);
        }
    });

    function enterEditMode(row) {
        // Store original values before editing
        row.find('.editable-cell').each(function() {
            var cell = $(this);
            var input = cell.find('.edit-input');
            var currentValue = input.val();
            input.data('original-value', currentValue);
        });

        row.addClass('editing');
        row.find('.display-value').hide();
        row.find('.edit-input').show().first().focus();
        row.find('.normal-actions').hide();
        row.find('.edit-actions').show();
    }

    function cancelEdit(row) {
        row.removeClass('editing');
        row.find('.edit-input').hide();
        row.find('.display-value').show();
        row.find('.edit-actions').hide();
        row.find('.normal-actions').show();

        // Reset input values to original (stored in data attributes)
        row.find('.editable-cell').each(function() {
            var cell = $(this);
            var input = cell.find('.edit-input');
            var originalValue = input.data('original-value');
            if (originalValue !== undefined) {
                input.val(originalValue);
            }
        });
    }

    function saveRow(row) {
        var id = row.data('id');
        var oldPathInput = row.find('[data-field="old_path"] .edit-input');
        var newPathInput = row.find('[data-field="new_path"] .edit-input');

        if (!oldPathInput.length || !newPathInput.length) {
            alert('<?php _e('Error: Could not find input fields.', 'redirect-without-code'); ?>');
            return;
        }

        var oldPath = oldPathInput.val().trim();
        var newPath = newPathInput.val().trim();

        // Basic validation
        if (!oldPath || !newPath) {
            alert('<?php _e('Both old path and new path are required.', 'redirect-without-code'); ?>');
            oldPathInput.focus();
            return;
        }

        if (oldPath === newPath) {
            alert('<?php _e('Old path and new path cannot be the same.', 'redirect-without-code'); ?>');
            newPathInput.focus();
            return;
        }

        // Check if rwc_ajax is available
        if (typeof rwc_ajax === 'undefined') {
            alert('<?php _e('Error: AJAX configuration not found.', 'redirect-without-code'); ?>');
            return;
        }

        // Show loading state
        row.find('.save-btn').text('<?php _e('Saving...', 'redirect-without-code'); ?>').prop('disabled', true);

        // AJAX save
        $.post(rwc_ajax.ajaxurl, {
            action: 'rwc_update_redirect',
            id: id,
            old_path: oldPath,
            new_path: newPath,
            _wpnonce: rwc_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                // Update display values
                row.find('[data-field="old_path"] .display-value code').text(oldPath);
                row.find('[data-field="new_path"] .display-value code').text(newPath);

                // Exit edit mode
                row.removeClass('editing');
                row.find('.edit-input').hide();
                row.find('.display-value').show();
                row.find('.edit-actions').hide();
                row.find('.normal-actions').show();

                // Show success feedback
                row.addClass('updated');
                setTimeout(function() {
                    row.removeClass('updated');
                }, 2000);
            } else {
                alert(response.data || '<?php _e('Error updating redirect.', 'redirect-without-code'); ?>');
            }
        })
        .fail(function() {
            alert('<?php _e('Error updating redirect.', 'redirect-without-code'); ?>');
        })
        .always(function() {
            row.find('.save-btn').text('<?php _e('Save', 'redirect-without-code'); ?>').prop('disabled', false);
        });
    }
});
</script>
