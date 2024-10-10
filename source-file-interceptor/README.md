# Source File Interceptor

## Description

Source File Interceptor is a WordPress plugin that intercepts and logs outgoing requests, providing network security telemetry. It now includes dynamic domain reputation checking using the VirusTotal API.

## Features

- Intercepts outgoing WordPress requests
- Logs request details to Elasticsearch
- Performs dynamic domain reputation checking using VirusTotal API
- Configurable through WordPress admin interface

## Installation

1. Upload the `source-file-interceptor` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings in the WordPress admin area

## Configuration

1. Go to Settings > Source File Interceptor in the WordPress admin area
2. Enter your VirusTotal API key
3. Save the settings

## Usage

Once configured, the plugin will automatically:

1. Intercept outgoing WordPress requests
2. Check the reputation of the domain using the VirusTotal API
3. Log the request details and domain reputation to Elasticsearch

No further action is required from the user after initial setup.

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- VirusTotal API key
- Elasticsearch instance for logging

## Changelog

### 1.1.0
- Added dynamic domain reputation checking using VirusTotal API
- Updated admin interface to include VirusTotal API key configuration

### 1.0.0
- Initial release

## Support

For support, please open an issue on the plugin's GitHub repository.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
