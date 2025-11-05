#!/bin/bash

# CleanCut Automation Setup Script
# This script sets up the automated features and real-time capabilities

echo "🚀 Setting up CleanCut Automation & Real-time Features..."
echo "=================================================="

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    echo "   Download from: https://nodejs.org/"
    exit 1
fi

echo "✅ Node.js found: $(node --version)"

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm first."
    exit 1
fi

echo "✅ npm found: $(npm --version)"

# Install WebSocket server dependencies
echo "📦 Installing WebSocket server dependencies..."
npm install

if [ $? -eq 0 ]; then
    echo "✅ Dependencies installed successfully"
else
    echo "❌ Failed to install dependencies"
    exit 1
fi

# Create systemd service for WebSocket server (Linux)
if command -v systemctl &> /dev/null; then
    echo "🔧 Creating systemd service for WebSocket server..."
    
    cat > /tmp/cleancut-websocket.service << EOF
[Unit]
Description=CleanCut WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=$(pwd)
ExecStart=/usr/bin/node websocket_server.js
Restart=always
RestartSec=10
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
EOF

    sudo mv /tmp/cleancut-websocket.service /etc/systemd/system/
    sudo systemctl daemon-reload
    sudo systemctl enable cleancut-websocket
    echo "✅ Systemd service created"
else
    echo "⚠️  Systemd not found. You'll need to start the WebSocket server manually."
fi

# Create cron jobs for automation
echo "⏰ Setting up cron jobs for automation..."

# Create cron script
cat > /tmp/cleancut-cron.sh << 'EOF'
#!/bin/bash

# CleanCut Automation Cron Jobs
# This script runs automated tasks

BASE_URL="http://localhost/CleanCut"
CRON_TOKEN="cleancut_cron_2024"

# Function to run cron job
run_cron_job() {
    local endpoint=$1
    local description=$2
    
    echo "$(date): Running $description..."
    curl -s "$BASE_URL/cron/$endpoint?token=$CRON_TOKEN" > /dev/null
    if [ $? -eq 0 ]; then
        echo "$(date): $description completed successfully"
    else
        echo "$(date): $description failed"
    fi
}

# Run different automation tasks based on time
HOUR=$(date +%H)
MINUTE=$(date +%M)

# Every 15 minutes: appointment automation
if [ $((MINUTE % 15)) -eq 0 ]; then
    run_cron_job "appointments" "Appointment Automation"
fi

# Every hour: full automation
if [ $MINUTE -eq 0 ]; then
    run_cron_job "run-all" "Full Automation Tasks"
fi

# Daily at 6 PM: send reminders
if [ $HOUR -eq 18 ] && [ $MINUTE -eq 0 ]; then
    run_cron_job "reminders" "Appointment Reminders"
fi

# Daily at 11 PM: earnings summaries
if [ $HOUR -eq 23 ] && [ $MINUTE -eq 0 ]; then
    run_cron_job "earnings" "Earnings Summaries"
fi

# Daily at 2 AM: data cleanup
if [ $HOUR -eq 2 ] && [ $MINUTE -eq 0 ]; then
    run_cron_job "cleanup" "Data Cleanup"
fi
EOF

chmod +x /tmp/cleancut-cron.sh
sudo mv /tmp/cleancut-cron.sh /usr/local/bin/cleancut-cron.sh

# Add to crontab
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/cleancut-cron.sh >> /var/log/cleancut-cron.log 2>&1") | crontab -

echo "✅ Cron jobs configured"

# Create log directory
sudo mkdir -p /var/log/cleancut
sudo chown www-data:www-data /var/log/cleancut

# Create environment file
echo "🔧 Creating environment configuration..."
cat > .env.automation << EOF
# CleanCut Automation Configuration
CRON_TOKEN=cleancut_cron_2024
WEBSOCKET_PORT=8080
WEBSOCKET_HOST=localhost
AUTO_CONFIRM_ENABLED=true
AUTO_COMPLETE_ENABLED=true
AUTO_CANCEL_ENABLED=true
REMINDER_ENABLED=true
EOF

echo "✅ Environment configuration created"

# Test WebSocket server
echo "🧪 Testing WebSocket server..."
timeout 5s node websocket_server.js &
WS_PID=$!
sleep 2

if ps -p $WS_PID > /dev/null; then
    echo "✅ WebSocket server started successfully"
    kill $WS_PID
else
    echo "❌ WebSocket server failed to start"
fi

# Test cron endpoint
echo "🧪 Testing cron endpoints..."
curl -s "http://localhost/CleanCut/cron/test" > /dev/null
if [ $? -eq 0 ]; then
    echo "✅ Cron endpoints are accessible"
else
    echo "⚠️  Cron endpoints may not be accessible. Check your web server configuration."
fi

echo ""
echo "🎉 Setup completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Start the WebSocket server:"
echo "   sudo systemctl start cleancut-websocket"
echo "   # OR manually: node websocket_server.js"
echo ""
echo "2. Check cron job status:"
echo "   crontab -l"
echo ""
echo "3. Monitor logs:"
echo "   tail -f /var/log/cleancut-cron.log"
echo ""
echo "4. Test automation:"
echo "   curl 'http://localhost/CleanCut/cron/test'"
echo ""
echo "5. Check system status:"
echo "   curl 'http://localhost/CleanCut/cron/status'"
echo ""
echo "🔧 Configuration files created:"
echo "   - .env.automation (automation settings)"
echo "   - /etc/systemd/system/cleancut-websocket.service"
echo "   - /usr/local/bin/cleancut-cron.sh"
echo ""
echo "📚 Documentation:"
echo "   - WebSocket server: websocket_server.js"
echo "   - Client integration: public/assets/js/websocket-client.js"
echo "   - Automation controller: app/Controllers/AutomatedAppointmentController.php"
echo "   - Background jobs: app/Services/BackgroundJobProcessor.php"
echo "   - Cron controller: app/Controllers/CronController.php"
echo ""
echo "✨ Your CleanCut system now has full automation and real-time capabilities!"
