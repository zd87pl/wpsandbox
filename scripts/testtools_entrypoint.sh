#!/bin/bash

# Process the captured data every 5 minutes
while true; do
    sleep 300
    echo "Processing API calls..."
    python /usr/local/bin/process_api_calls.py /var/www/html/tcpdump_output/api_calls.pcap /logs/api_calls.csv
    echo "API calls processed and saved to /logs/api_calls.csv"
done