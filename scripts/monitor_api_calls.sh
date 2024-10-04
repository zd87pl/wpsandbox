#!/bin/bash

# Directory to store tcpdump output
TCPDUMP_DIR="/var/www/html/logs/tcpdump_output"
mkdir -p "$TCPDUMP_DIR"

# Start tcpdump to capture HTTP traffic
echo "Starting tcpdump to capture API calls..."
tcpdump -i eth0 -w "$TCPDUMP_DIR/api_calls.pcap" -s0 port 80 or port 443 &

# Keep the script running
while true; do
    sleep 60
done