import sys
import csv
from scapy.all import rdpcap, IP, TCP, Raw
import re
import pandas as pd

def extract_plugin_name(user_agent):
    match = re.search(r'WordPress/[^;]+;\s+([^;]+)', user_agent)
    return match.group(1) if match else 'Unknown'

def process_pcap(pcap_file, csv_file):
    packets = rdpcap(pcap_file)
    api_calls = []

    for packet in packets:
        if IP in packet and TCP in packet and Raw in packet:
            payload = packet[Raw].load.decode('utf-8', errors='ignore')
            if 'GET' in payload or 'POST' in payload:
                lines = payload.split('\r\n')
                request_line = lines[0]
                host = next((line.split(': ')[1] for line in lines if line.startswith('Host: ')), '')
                user_agent = next((line.split(': ')[1] for line in lines if line.startswith('User-Agent: ')), '')
                
                plugin = extract_plugin_name(user_agent)
                endpoint = f"{host}{request_line.split()[1]}"
                
                api_calls.append({
                    'plugin': plugin,
                    'endpoint': endpoint,
                    'method': request_line.split()[0],
                    'timestamp': packet.time
                })

    df = pd.DataFrame(api_calls)
    df.to_csv(csv_file, index=False)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python process_api_calls.py <pcap_file> <csv_file>")
        sys.exit(1)

    pcap_file = sys.argv[1]
    csv_file = sys.argv[2]
    process_pcap(pcap_file, csv_file)