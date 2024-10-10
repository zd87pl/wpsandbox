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

## Setup and Usage

1. Clone this repository
2. Navigate to the project directory
3. Place your Google Cloud credentials file (for Gemini API access) in the root directory as `google-credentials.json`
4. Run `docker-compose up -d` to start the WordPress environment and all services
5. Access the WordPress admin panel and activate the Source File Interceptor plugin
6. Configure the Source File Interceptor plugin with your VirusTotal API key
7. Install or activate any WordPress plugins you want to analyze
8. The Code Analysis Service will automatically analyze newly installed plugins
9. Use Kibana to visualize and analyze the logged data from both the Source File Interceptor and Code Analysis Service

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

Future improvements for the FakeWoo project:

1. Real-time monitoring and alerting
   - Implement WebSocket server for live updates
   - Set up configurable alerts for suspicious activities
   - Integrate with notification services (Slack, email)

2. Machine learning integration for anomaly detection
   - Implement ML models for detecting unusual plugin behavior
   - Use historical data for personalized anomaly detection
   - Integrate with cloud-based ML services

3. Expanded code analysis capabilities
   - Implement more sophisticated static code analysis
   - Add dynamic code analysis for runtime behavior
   - Include vulnerability scanning against CVE databases

4. User-friendly dashboard in WordPress admin
   - Create custom admin page with activity overview
   - Implement interactive charts and graphs
   - Add filtering and search capabilities

5. Performance optimization
   - Implement caching mechanisms
   - Optimize database queries and Elasticsearch indexing
   - Support distributed processing of analysis tasks

6. Integration with more security services
   - Expand beyond VirusTotal
   - Create plugin system for adding new security services

7. Automated reporting
   - Generate periodic summary reports
   - Implement exportable reports (PDF, CSV, JSON)

8. Plugin behavior simulation
   - Create sandboxed environment for plugin simulation
   - Record and analyze behavior in various scenarios

9. Collaborative analysis features
   - Implement user roles and permissions
   - Add commenting and tagging for investigations

10. API for external integrations
    - Develop RESTful API for external access
    - Provide webhooks for real-time data pushing

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL v2 or later.

## Changelog

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
