# FakeWoo Project

## Overview

FakeWoo is a comprehensive project designed to simulate and analyze WordPress plugin behavior in a controlled environment. It includes various components for intercepting, logging, and analyzing network requests made by WordPress plugins, as well as performing code analysis on installed plugins.

## Components

### 1. Source File Interceptor Plugin

The Source File Interceptor is a WordPress plugin that intercepts outgoing requests and provides network security telemetry.

#### Features:
- Dynamic domain reputation checking using the VirusTotal API
- Configurable through WordPress admin interface
- Logs request details and domain reputation scores to Elasticsearch

For more details, see the [Source File Interceptor README](./source-file-interceptor/README.md).

### 2. Code Analysis Service

A Python-based service that analyzes the code of installed WordPress plugins using the Gemini API.

#### Features:
- Automatic analysis of plugin code for security issues, code quality, and best practices
- Integration with Google Cloud's Gemini API for advanced code analysis
- Results stored in Elasticsearch for further analysis and visualization

### 3. Docker Environment

The project uses Docker to create a controlled WordPress environment for testing and analysis.

### 4. Elasticsearch and Kibana

Used for storing and visualizing logged data from the Source File Interceptor plugin and the Code Analysis Service.

### 5. Environment Variable Manager

A WordPress plugin that allows easy management of environment variables through the WordPress admin interface.

#### Features:
- Update Gemini API Key, Elasticsearch Address, and VirusTotal API Key
- Automatically updates the .env file and Docker environment

### 6. FakeWoo API

A WordPress plugin that provides API endpoints for the FakeWoo frontend.

#### Features:
- Retrieve list of installed plugins
- Get VirusTotal scoring and static code analysis results for specific plugins
- Update environment variables

### 7. Frontend Dashboard

A single-page dashboard that provides an interface for managing environment variables and viewing plugin analysis results.

#### Features:
- Update environment variables
- Select and view analysis results for installed plugins

## Setup and Usage

1. Clone this repository
2. Navigate to the project directory
3. Create a `.env` file in the root directory with the following content:
   ```
   GEMINI_API_KEY=your_gemini_api_key
   ELASTIC_ADDRESS=http://elasticsearch:9200
   VIRUSTOTAL_API_KEY=your_virustotal_api_key
   ```
4. Place your Google Cloud credentials file (for Gemini API access) in the root directory as `google-credentials.json`
5. Run `docker-compose up -d` to start the WordPress environment and all services
6. Access the WordPress admin panel (http://localhost:8080/wp-admin)
7. Activate the following plugins:
   - Source File Interceptor
   - Environment Variable Manager
   - FakeWoo API
8. Access the FakeWoo Dashboard (http://localhost:3000)
9. Use the dashboard to update environment variables and view plugin analysis results
10. Install or activate any WordPress plugins you want to analyze
11. The Code Analysis Service will automatically analyze newly installed plugins

## Using the Frontend Dashboard

1. Open the FakeWoo Dashboard at http://localhost:3000
2. In the "Environment Variables" section, you can update the Gemini API Key, Elasticsearch Address, and VirusTotal API Key
3. In the "Plugin Analysis" section, select a plugin from the dropdown menu to view its VirusTotal scoring and static code analysis results

## Managing Environment Variables

1. Use the "Environment Variables" section on the FakeWoo Dashboard to update variables
2. Click "Save Changes" to update the variables
3. The changes will be reflected in the `.env` file and the Docker environment
4. Restart the Docker containers using `docker-compose down` followed by `docker-compose up -d` to apply the changes

## Data Organization in Elasticsearch

- Network logs: indexed as `plugin-{plugin_name}-logs`
- VirusTotal reputation scores: included in the network logs
- Code analysis results: indexed as `plugin-{plugin_name}-analysis`

To visualize this data in Kibana:

1. Access Kibana (usually at http://localhost:5601)
2. Create index patterns for `plugin-*-logs` and `plugin-*-analysis`
3. Create dashboards and visualizations using these index patterns
4. Use the `plugin_name` field to filter data for specific plugins

## Requirements

- Docker and Docker Compose
- VirusTotal API key
- Google Cloud account with Gemini API access
- Elasticsearch and Kibana (included in Docker setup)

## TODO

[The TODO section remains unchanged]

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL v2 or later.

## Changelog

### 1.4.0
- Added FakeWoo API plugin for frontend communication
- Implemented frontend dashboard for environment variable management and plugin analysis viewing
- Updated documentation to reflect new features and usage instructions

### 1.3.0
- Added Environment Variable Manager for easy configuration of API keys and addresses
- Updated Docker setup to use environment variables
- Improved documentation for environment variable management

### 1.2.0
- Added Code Analysis Service using Gemini API
- Updated Docker setup to include the new service
- Enhanced data organization in Elasticsearch for per-plugin analysis
- Improved documentation and setup instructions

### 1.1.0
- Added dynamic domain reputation checking to Source File Interceptor plugin
- Updated Source File Interceptor admin interface for VirusTotal API key configuration
- Enhanced logging capabilities with domain reputation scores

### 1.0.0
- Initial release of FakeWoo project
- Basic request interception and logging functionality

## Support

For support, please open an issue on the project's GitHub repository.
