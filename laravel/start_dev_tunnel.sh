#!/bin/bash
# Development Tunnel - Expose localhost:8000 to Arduino
# Run this when you want Arduino to send to your local machine

echo "================================================"
echo "  🚇 Starting Development Tunnel"
echo "================================================"
echo ""
echo "This will expose your localhost:8000 to the internet"
echo "so your Arduino can send data to your local machine."
echo ""

# Check if docker is running
if ! docker ps &> /dev/null; then
    echo "❌ Docker is not running!"
    echo "Please start Docker first."
    exit 1
fi

# Check if containers are running
if ! docker ps | grep -q laravel_app; then
    echo "⚠️  Laravel containers not running. Starting them..."
    docker-compose up -d
    echo "✅ Containers started!"
    echo ""
fi

# Start localtunnel
echo "🌐 Starting LocalTunnel..."
echo ""
echo "Getting your tunnel URL..."
echo ""

# Install lt if not installed
if ! command -v lt &> /dev/null; then
    echo "📦 Installing LocalTunnel..."
    npm install -g localtunnel
fi

# Start tunnel
lt --port 8000 --print-requests --subdomain gps-tracker-${USER}

echo ""
echo "================================================"
echo "  Tunnel closed"
echo "================================================"

