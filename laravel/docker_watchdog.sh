#!/bin/bash
# Docker Watchdog - Monitors and auto-restarts containers
# This script runs every minute via cron to ensure containers stay up

LOG_FILE="/home/ubuntu/gpstracker/laravel/watchdog.log"
PROJECT_DIR="/home/ubuntu/gpstracker/laravel"

# Navigate to project directory
cd "$PROJECT_DIR" || exit 1

# Initialize log file if it doesn't exist
touch "$LOG_FILE"

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Log startup
log "🐕 Watchdog check started"

# Check if containers are running
check_container() {
    local container_name=$1
    if ! docker ps --format '{{.Names}}' | grep -q "^${container_name}$"; then
        return 1  # Container is not running
    fi
    return 0  # Container is running
}

# Restart a container
restart_container() {
    local container_name=$1
    log "⚠️  Container $container_name is down! Restarting..."
    docker-compose up -d "$container_name"
    sleep 5
    
    if check_container "$container_name"; then
        log "✅ Container $container_name restarted successfully"
        return 0
    else
        log "❌ Failed to restart $container_name"
        return 1
    fi
}

# Check Laravel app container
if ! check_container "laravel_app"; then
    restart_container "app"
fi

# Check MySQL container
if ! check_container "laravel_db"; then
    restart_container "db"
fi

# Check if app is responding (optional - more thorough check)
if check_container "laravel_app"; then
    # Try to curl the health endpoint
    if ! curl -f -s http://localhost:8000/api/location/latest > /dev/null 2>&1; then
        log "⚠️  Laravel app not responding! Restarting..."
        docker-compose restart app
        sleep 5
        if curl -f -s http://localhost:8000/api/location/latest > /dev/null 2>&1; then
            log "✅ Laravel app restarted and responding"
        else
            log "❌ Laravel app still not responding after restart"
        fi
    fi
fi

# Check system resources
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100}')
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')

if [ "$MEMORY_USAGE" -gt 90 ]; then
    log "⚠️  High memory usage: ${MEMORY_USAGE}%"
    # Clean up Docker if memory is too high
    docker system prune -f >> "$LOG_FILE" 2>&1
fi

if [ "$DISK_USAGE" -gt 85 ]; then
    log "⚠️  High disk usage: ${DISK_USAGE}%"
fi

# Keep log file size manageable (last 1000 lines)
if [ -f "$LOG_FILE" ] && [ -s "$LOG_FILE" ]; then
    tail -n 1000 "$LOG_FILE" > "${LOG_FILE}.tmp" && mv "${LOG_FILE}.tmp" "$LOG_FILE"
fi

# Log completion
log "✅ Watchdog check completed"

