#!/bin/bash

# Check if a CSV file is provided
if [ $# -eq 0 ]; then
    echo "No CSV file provided. Skipping plugin installation."
    exit 0
fi

CSV_FILE=$1

# Read CSV file and install plugins
while IFS=',' read -r plugin_name plugin_url
do
    plugin_name=$(echo "$plugin_name" | tr -d '[:space:]')
    if [ -n "$plugin_name" ]; then
        if [ -n "$plugin_url" ]; then
            wp plugin install "$plugin_url" --activate --allow-root
        else
            wp plugin install "$plugin_name" --activate --allow-root
        fi
    fi
done < "$CSV_FILE"

# Update all plugins
wp plugin update --all --allow-root

echo "All plugins installed and updated successfully."