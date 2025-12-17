#!/bin/bash
# Quick Deploy Script for EC2
# Run: ./deploy_to_ec2.sh

set -e  # Exit on error

echo "========================================="
echo "  🚀 GPS Tracker - Deploy to EC2"
echo "========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "docker-compose.yaml" ]; then
    echo "❌ Error: Please run this script from the laravel directory"
    exit 1
fi

# Step 1: Git status
echo "📋 Step 1: Checking git status..."
git status

echo ""
read -p "Do you want to commit and push changes? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Step 2: Commit changes
    echo ""
    echo "📝 Step 2: Committing changes..."
    read -p "Enter commit message: " commit_msg
    git add .
    git commit -m "$commit_msg"
    
    # Step 3: Push to GitHub
    echo ""
    echo "⬆️ Step 3: Pushing to GitHub..."
    git push origin main
    
    echo ""
    echo "✅ Changes pushed to GitHub!"
fi

# Step 4: Deploy to EC2
echo ""
echo "🌐 Step 4: Deploying to EC2..."
echo ""

ssh -i "gps-tracker.pem" ubuntu@54.254.135.119 << 'EOF'
    echo "Connected to EC2..."
    echo ""
    
    cd ~/gpstracker/laravel
    
    echo "📥 Pulling latest changes..."
    git pull origin main
    
    echo ""
    echo "🔄 Restarting containers..."
    docker-compose restart app
    
    echo ""
    echo "✅ Deployment complete!"
    echo ""
    echo "Check your site at: http://54.254.135.119/"
EOF

echo ""
echo "========================================="
echo "  🎉 Deployment Successful!"
echo "========================================="
echo ""
echo "Your GPS Tracker is now live with:"
echo "  ✅ Path Trail"
echo "  ✅ Speedometer"
echo "  ✅ Live Notifications"
echo ""
echo "View at: http://54.254.135.119/"
echo ""

