#!/bin/bash
set -e

# Wait for the database to be ready
until wp db check --allow-root; do
  >&2 echo "Database is unavailable - sleeping"
  sleep 1
done

>&2 echo "Database is up - executing command"

# Install WooCommerce if not already installed
if ! $(wp plugin is-installed woocommerce --allow-root); then
  wp plugin install woocommerce --activate --allow-root
fi

# Check if a plugins CSV file exists and install plugins
if [ -f "/var/www/html/plugins.csv" ]; then
  /usr/local/bin/install_plugins.sh /var/www/html/plugins.csv
fi

# Start monitoring API calls in the background
/usr/local/bin/monitor_api_calls.sh &

# Start PHP server for API in the background
php -S 0.0.0.0:8000 /var/www/html/api.php &

# Execute the main container command
exec "$@"