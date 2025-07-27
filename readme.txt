=== Redirect Without Code ===
Contributors: joachimbrindeau
Tags: redirects, migration, seo, 301, csv
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import CSV files and create 301 redirects without writing any code. Perfect for website migrations and URL structure changes.

== Description ==

**Redirect Without Code** is the perfect solution for website migrations, redesigns, and URL structure changes. This plugin automatically imports CSV files and creates 301 redirects to preserve your SEO rankings and user experience - no coding required!

### Key Features

* **Flexible CSV Import**: Works with any CSV containing path_old, path_new, status columns
* **Manual Addition**: Add individual redirects through a simple form
* **Smart Filtering**: Only imports rows with "301" status from CSV files
* **Duplicate Prevention**: Automatically skips duplicate old paths
* **Enable/Disable**: Toggle redirects on/off without deleting them
* **Clean Interface**: User-friendly WordPress admin interface
* **SEO Friendly**: Proper 301 redirects preserve link juice and rankings
* **No Coding Required**: Set up redirects without touching any code

### Perfect For

* Website migrations and redesigns
* Domain changes and consolidations
* URL structure overhauls
* Content management system switches
* E-commerce platform migrations
* SEO optimization projects

### How It Works

1. **Generate CSV**: Create a CSV file with your redirect mappings
2. **Import CSV**: Upload the CSV file through the plugin interface
3. **Review Redirects**: Check imported redirects in the admin panel
4. **Automatic Redirects**: Plugin handles 301 redirects automatically
5. **Manage**: Enable, disable, or delete redirects as needed

### CSV Format

The plugin works with any CSV file containing these required columns:
- `path_old` - The old URL path to redirect from
- `path_new` - The new URL path to redirect to
- `status` - Must be "301" for the row to be imported

**Examples:**
- Simple: `path_old,path_new,status`
- Extended: `domain_old,domain_new,path_old,path_new,status`

Column order doesn't matter - the plugin automatically detects column positions.

### Technical Details

* Creates a custom database table for redirect storage
* Handles redirects at the WordPress template level
* Supports path normalization and validation
* Includes proper nonce security for all actions
* Translation ready with text domain support

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/redirect-without-code/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Tools → Redirect Without Code to start using the plugin

== Frequently Asked Questions ==

= What CSV format does the plugin expect? =

The plugin works with any CSV containing path_old, path_new, and status columns. Column order doesn't matter. Only rows with status "301" are imported.

= Can I add redirects manually? =

Yes! The plugin includes a form to add individual redirects manually.

= Will this affect my site performance? =

The plugin is optimized for performance. Redirects are stored in a database table with proper indexing, and lookups only occur for URLs that don't match existing WordPress content.

= Can I disable redirects without deleting them? =

Absolutely! Each redirect can be toggled on/off individually, allowing you to test and manage redirects without losing the data.

= Does this work with caching plugins? =

Yes, the plugin handles redirects at the WordPress template level, which works with most caching solutions. However, you may need to clear your cache after importing redirects.

= Can I export my redirects? =

Currently, the plugin focuses on importing. You can view all redirects in the admin interface and manage them individually.

== Screenshots ==

1. Main admin interface showing redirect management
2. CSV import form with flexible format options
3. Manual redirect addition form
4. Redirect listing with enable/disable controls

== Changelog ==

= 1.0.0 =
* Initial release
* Flexible CSV import functionality (works with any column order)
* Manual redirect addition
* Enable/disable redirect controls
* WordPress.org repository compliance
* Full width admin interface
* Minimalist approach - only requires 3 essential columns

== Upgrade Notice ==

= 1.0.0 =
Initial release of Redirect Without Code.

== Support ==

For support, feature requests, or bug reports, please visit the plugin's support forum or GitHub repository.

== Privacy ==

This plugin does not collect, store, or transmit any personal data. It only processes URL redirect mappings that you provide through CSV imports or manual entry.
