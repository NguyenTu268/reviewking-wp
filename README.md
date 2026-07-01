# reviewking.info

This repository contains the WordPress site files for `reviewking.info`.

## Overview

`reviewking.info` is a WordPress-based website. This repository includes the WordPress core files, configuration file, and the `wp-content` directory where themes, plugins, uploads, and other custom site data are stored.

## Repository contents

- `wp-admin/`, `wp-includes/`, and root WordPress files: WordPress core.
- `wp-content/`: themes, plugins, uploads, cache, and site-specific customizations.
- `wp-config-sample.php`: sample configuration template (see Deployment below).
- `license.txt`: WordPress license information.
- `readme.html`: original WordPress readme file.

## Deployment

1. Clone or download this repository.
2. Place the files on a PHP/MySQL-enabled web server.
3. Copy `wp-config-sample.php` to `wp-config.php` and fill in your database credentials and secret keys/salts (generate new ones at the [WordPress secret-key service](https://api.wordpress.org/secret-key/1.1/salt/)).
4. Access the site in a browser and complete any WordPress setup or updates.

## Notes

- `wp-config.php` is intentionally excluded from version control (see `.gitignore`) because it holds real database credentials and secret keys — never commit it.
- The site uses caching and security plugins under `wp-content/`.

## License

WordPress is released under the GNU General Public License v2 or later. See `license.txt` for details.
