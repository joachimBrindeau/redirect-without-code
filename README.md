# Redirect Without Code

A WordPress plugin that imports CSV files and creates 301 redirects without writing any code. Perfect for website migrations, redesigns, and URL structure changes.

## Features

- **Flexible CSV Import**: Works with any CSV containing path_old, path_new, status columns
- **Manual Addition**: Add individual redirects through a simple form
- **Smart Filtering**: Only imports rows with "301" status from CSV files
- **Duplicate Prevention**: Automatically skips duplicate old paths
- **Enable/Disable**: Toggle redirects on/off without deleting them
- **Clean Interface**: User-friendly WordPress admin interface
- **SEO Friendly**: Proper 301 redirects preserve link juice and rankings
- **No Coding Required**: Set up redirects without touching any code

## Perfect For

- Website migrations and redesigns
- Domain changes and consolidations
- URL structure overhauls
- Content management system switches
- E-commerce platform migrations
- SEO optimization projects

## Installation

### From WordPress.org (Recommended)
1. Go to Plugins → Add New in your WordPress admin
2. Search for "Redirect Without Code"
3. Install and activate the plugin

### Manual Installation
1. Download the plugin files
2. Upload to `/wp-content/plugins/redirect-without-code/`
3. Activate through the WordPress admin

## Usage

### CSV Import
1. Go to **Tools → Redirect Without Code**
2. Upload your CSV file using the import form
3. Configure import options (clear existing, skip duplicates)
4. Click "Import Redirects"

### Manual Addition
1. Go to **Tools → Redirect Without Code**
2. Fill out the "Add Single Redirect" form
3. Enter old path and new path
4. Click "Add Redirect"

### Managing Redirects
- **Enable/Disable**: Toggle redirects without deleting
- **Delete**: Remove unwanted redirects permanently

## CSV Format

The plugin works with any CSV file that contains these **required columns**:
- `path_old` - The old URL path to redirect from
- Fanew_path` - The new URL path to redirect to  
- `status` - Must be "301" for the row to be imported

**Flexible formats supported:**

**Minimal format:**
```csv
path_old,path_new,status
/old-page,/new-page,301
/about,/about-us,301
```

**Extended format:**
```csv
domain_old,domain_new,path_old,path_new,status
old-site.com,new-site.com,/old-page,/new-page,301
old-site.com,new-site.com,/about,/about-us,301
```

The plugin automatically detects column positions, so column order doesn't matter. Only rows with `status = "301"` will be imported.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Development

### File Structure
```
redirect-without-code/
├── redirect-without-code.php       # Main plugin file
├── templates/
│   └── admin-page.php              # Admin interface template
├── assets/
│   └── admin.css                   # Admin styles
├── languages/
│   └── redirect-without-code.pot   # Translation template
├── uninstall.php                   # Cleanup on uninstall
├── readme.txt                        # WordPress.org readme
└── README.md                         # This file
```

### Database Schema
The plugin creates a `wp_sitemap_redirects` table:
```sql
CREATE TABLE wp_sitemap_redirects (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    old_path varchar(500) NOT NULL,
    new_path varchar(500) NOT NULL,
    status varchar(20) DEFAULT '301',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    active tinyint(1) DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY old_path (old_path),
    KEY active (active)
);
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Support

- [WordPress.org Support Forum](https://wordpress.org/support/plugin/redirect-without-code/)
- [GitHub Issues](https://github.com/joachimBrindeau/redirect-without-code/issues)

## Changelog

### 1.0.0
- Initial release
- Flexible CSV import functionality (works with any column order)
- Manual redirect addition
- Enable/disable redirect controls
- WordPress.org repository compliance
- Full width admin interface
- Minimalist approach - only requires 3 essential columns