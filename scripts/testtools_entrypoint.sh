#!/bin/bash

# Process the captured data every 5 minutes
while true; do
    sleep 60
    echo "Processing API calls..."
    python /usr/local/bin/process_api_calls.py /logs/api_calls.pcap /logs/api_calls.csv
    echo "API calls processed and saved to /logs/api_calls.csv"
done
