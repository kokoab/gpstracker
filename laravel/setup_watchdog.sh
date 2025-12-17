#!/bin/bash
# Setup Docker Watchdog on EC2
# Run this once on EC2 to set up automatic container monitoring

set -e

PROJECT_DIR="/home/ubuntu/gpstracker/laravel"
WATCHDOG_SCRIPT="$PROJECT_DIR/docker_watchdog.sh"

echo "========================================="
echo "  🐕 Setting Up Docker Watchdog"
echo "========================================="
echo ""

# Make watchdog script executable
chmod +x "$WATCHDOG_SCRIPT"

# Add to crontab (runs every minute)
(crontab -l 2>/dev/null | grep -v "$WATCHDOG_SCRIPT"; echo "* * * * * $WATCHDOG_SCRIPT") | crontab -

echo "✅ Watchdog installed!"
echo ""
echo "The watchdog will:"
echo "  - Check containers every minute"
echo "  - Auto-restart if they're down"
echo "  - Monitor system resources"
echo "  - Log all actions to: $PROJECT_DIR/watchdog.log"
echo ""
echo "View logs:"
echo "  tail -f $PROJECT_DIR/watchdog.log"
echo ""
echo "Test watchdog manually:"
echo "  $WATCHDOG_SCRIPT"
echo ""

