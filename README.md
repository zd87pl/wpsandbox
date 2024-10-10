# WooCommerce Testing Sandbox in Docker

This project sets up a Docker-based testing environment for WooCommerce, allowing you to install plugins, monitor API calls, and analyze the interactions between plugins and WordPress/WooCommerce servers. It also includes a frontend dashboard for easy management. This setup is compatible with ARM64 architecture, including Apple Silicon Macs.

## Setup

1. Make sure you have Docker and Docker Compose installed on your system.

2. Clone this repository to your local machine.

3. Build and start the containers:

   ```sh
   docker-compose up --build
   ```

   Note for Apple Silicon (M1/M2) users: The setup is configured to work with ARM64 architecture. No additional steps are required.

4. Once the containers are up and running, you can access:
   - The WordPress site at `http://localhost:8080`
   - The frontend dashboard at `http://localhost:3000`

5. To configure Kibana to provide a search UI for collected HTTP requests, perform the following
   1. Ensure that at least one outbound HTTP request has been issued from the WordPress install
      1. This can be triggered from the [Plugins > Add New Plugin](http://localhost:8080/wp-admin/plugin-install.php) page
   2. Open the Kibana dashboard at <http://localhost:5601>
   3. Navigate to the Stack Management page in the main sidebar
   4. Navigate to [Kibana > Data Views](http://localhost:5601/app/management/kibana/dataViews) in the Management sidebar
   5. Click `Create data view`
   6. Provide a name for the data view
   7. Set `Index pattern` to `api-calls`
   8. Set `Timestamp field`
   9. The data view will now be available when navigating to the [Analytics > Discover](http://localhost:5601/app/discover) page in the main sidebar

<!-- Github admonition syntax - see https://github.com/orgs/community/discussions/16925 -->
> [!NOTE]
> In the future, the Kibana data view will be automatically created

## Frontend Dashboard

The frontend dashboard provides an easy-to-use interface for managing your WooCommerce testing sandbox. It offers the following features:

- View the status of WordPress and Test Tools containers
- Check WordPress and WooCommerce versions
- See a list of installed plugins
- Upload a CSV file with plugins to install
- Trigger plugin installation

To use the plugin installation feature:

1. Create a CSV file with the list of plugins you want to install. The file should have the following format:

   ```csv
   plugin_name,plugin_url
   ```

   Example:

   ```csv
   jetpack,
   woocommerce-services,
   custom-plugin,https://example.com/custom-plugin.zip
   ```

   You can specify plugins in two ways:
   - Just the plugin name: The script will install the plugin from the WordPress.org repository.
   - Plugin name and URL: The script will install the plugin from the provided URL.

2. In the frontend dashboard, use the file upload input to select your CSV file.

3. Click the "Install Plugins" button to start the installation process.

## Functionality

### WordPress Container

- Automatically installs and activates WooCommerce.
- Installs and activates additional plugins specified in `plugins.csv` (if present).
- Monitors API calls made to WordPress and WooCommerce servers.
- Provides an API endpoint for the frontend dashboard.

### Database Container

- Uses MariaDB 10.5, which is compatible with ARM64 architecture and serves as a drop-in replacement for MySQL.

### Test Tools Container

- Captures network traffic related to API calls.
- Processes the captured data every 5 minutes.
- Generates a CSV file (`/logs/api_calls.csv`) with information about the API calls, including the plugin that made the call, the endpoint, the HTTP method, and the timestamp.

### ElasticSearch and Kibana Containers

- Stores data about HTTP requests made using the WordPress HTTP API
- Provide a frontend to browse/search captured HTTP request data

## Analyzing Results

The API call data is saved in `/logs/api_calls.csv` inside the test tools container. You can access this file by copying it from the container or by mounting a volume to the `/logs` directory in the `docker-compose.yml` file.

To copy the file from the container:

```sh
docker cp $(docker-compose ps -q test-tools):/logs/api_calls.csv ./api_calls.csv
```

## Customization

You can modify the scripts in the `scripts/` directory to add more functionality or change the behavior of the containers. Remember to rebuild the containers after making changes:

```sh
docker-compose up --build
```

## Troubleshooting

If you encounter any issues, check the Docker logs for each container:

```sh
docker-compose logs wordpress
docker-compose logs db
docker-compose logs test-tools
docker-compose logs frontend
```

These logs can provide valuable information about any errors or unexpected behavior in the setup.

For Apple Silicon (M1/M2) users:

- If you encounter any ARM64-related issues, make sure your Docker Desktop is up to date and configured to use the new Virtualization framework.
- The setup uses MariaDB instead of MySQL due to better ARM64 compatibility. This should not affect the functionality of the WordPress installation.

## Notes

- This setup is intended for testing purposes only and should not be used in a production environment.
- Make sure to comply with the licenses of all plugins you install and test.
- The API call monitoring may capture sensitive information. Ensure you handle the captured data securely and in compliance with relevant privacy regulations.

## Sample Products

In order to import sample products, proceed as following:

1. In wp-admin go to Products > Import
1. “Show advanced options”
1. Product path: ./wp-content/plugins/woocommerce/sample-data/sample_products.csv
1. Continue
1. Confirm all the columns mapping
1. Finish
