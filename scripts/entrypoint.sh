#!/bin/bash
set -e

# Wait for the database to be ready
until mysqladmin ping -h"$WORDPRESS_DB_HOST" --silent; do
    echo "Waiting for database to be ready..."
    sleep 1
done

# Check if WordPress is downloaded
if [ ! -f /var/www/html/wp-config.php ]; then
    echo "WordPress not found, downloading..."
    wp core download --allow-root
    wp config create --dbname="$WORDPRESS_DB_NAME" --dbuser="$WORDPRESS_DB_USER" --dbpass="$WORDPRESS_DB_PASSWORD" --dbhost="$WORDPRESS_DB_HOST" --allow-root
fi

# Check if WordPress is installed
if ! $(wp core is-installed --allow-root); then
    echo "WordPress is not installed. Installing now..."
    wp core install --url=${WORDPRESS_URL:-localhost} --title="WooCommerce Sandbox" --admin_user=${WORDPRESS_ADMIN_USER:-admin} --admin_password=${WORDPRESS_ADMIN_PASSWORD:-password} --admin_email=${WORDPRESS_ADMIN_EMAIL:-admin@example.com} --skip-email --allow-root
fi

# Install and activate WooCommerce if not already installed
if ! $(wp plugin is-installed woocommerce --allow-root); then
    echo "Installing and activating WooCommerce..."
    wp plugin install woocommerce --activate --allow-root
fi

# Check if a plugins CSV file exists and install plugins
if [ -f "/var/www/html/plugins.csv" ]; then
    echo "Installing plugins from CSV..."
    /usr/local/bin/install_plugins.sh /var/www/html/plugins.csv
fi

if [ -f "/var/www/html/wp-content/plugins/source-file-interceptor" ]; then
    echo "Enabling WP HTTP interceptor"
    wp plugin activate source-file-interceptor
fi

# Start monitoring API calls in the background
/usr/local/bin/monitor_api_calls.sh &

# Start PHP server for API in the background
php -S 0.0.0.0:8000 /var/www/html/api.php &

# Execute the main container command
exec "$@"
