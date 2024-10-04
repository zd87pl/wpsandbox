#!/bin/bash

# Start tcpdump to capture HTTP traffic
tcpdump -i eth0 -w /logs/api_calls.pcap -s0 port 80 or port 443 &

# Process the captured data every 5 minutes
while true; do
    sleep 300
    python3 /usr/local/bin/process_api_calls.py /logs/api_calls.pcap /logs/api_calls.csv
done