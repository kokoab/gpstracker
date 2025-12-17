#!/bin/bash
# GPS Tracker Monitor - Watch incoming data in real-time
# Usage: ./monitor_gps.sh

SERVER="http://54.254.135.119"

echo "============================================"
echo "   GPS TRACKER MONITOR"
echo "   Server: $SERVER"
echo "============================================"
echo ""

while true; do
    clear
    echo "============================================"
    echo "   GPS TRACKER - LATEST LOCATION"
    echo "   $(date '+%Y-%m-%d %H:%M:%S')"
    echo "============================================"
    echo ""
    
    # Get latest location
    RESPONSE=$(curl -s "$SERVER/api/location/latest")
    
    if [ $? -eq 0 ]; then
        echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
    else
        echo "❌ ERROR: Could not connect to server"
    fi
    
    echo ""
    echo "============================================"
    echo "Refreshing every 5 seconds... (Ctrl+C to stop)"
    echo "============================================"
    
    sleep 5
done

