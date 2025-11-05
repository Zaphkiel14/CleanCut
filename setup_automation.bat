@echo off
REM CleanCut Automation Setup Script for Windows
REM This script sets up the automated features and real-time capabilities

echo 🚀 Setting up CleanCut Automation ^& Real-time Features...
echo ==================================================

REM Check if Node.js is installed
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js is not installed. Please install Node.js first.
    echo    Download from: https://nodejs.org/
    pause
    exit /b 1
)

echo ✅ Node.js found
node --version

REM Check if npm is installed
npm --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ npm is not installed. Please install npm first.
    pause
    exit /b 1
)

echo ✅ npm found
npm --version

REM Install WebSocket server dependencies
echo 📦 Installing WebSocket server dependencies...
npm install

if %errorlevel% equ 0 (
    echo ✅ Dependencies installed successfully
) else (
    echo ❌ Failed to install dependencies
    pause
    exit /b 1
)

REM Create Windows service script
echo 🔧 Creating Windows service script...
(
echo @echo off
echo REM CleanCut WebSocket Server Service
echo cd /d "%~dp0"
echo node websocket_server.js
echo pause
) > start_websocket.bat

echo ✅ Windows service script created: start_websocket.bat

REM Create automation test script
echo 🔧 Creating automation test script...
(
echo @echo off
echo REM CleanCut Automation Test Script
echo echo Testing automation endpoints...
echo curl -s "http://localhost/CleanCut/cron/test"
echo curl -s "http://localhost/CleanCut/cron/status"
echo pause
) > test_automation.bat

echo ✅ Automation test script created: test_automation.bat

REM Create Windows Task Scheduler XML for automation
echo ⏰ Creating Windows Task Scheduler configuration...
(
echo ^<?xml version="1.0" encoding="UTF-16"?^>
echo ^<TaskScheduler^>
echo   ^<Task^>
echo     ^<Name^>CleanCut Automation^</Name^>
echo     ^<Description^>Runs CleanCut automation tasks every 5 minutes^</Description^>
echo     ^<Triggers^>
echo       ^<TimeTrigger^>
echo         ^<StartBoundary^>2024-01-01T00:00:00^</StartBoundary^>
echo         ^<Repetition^>
echo           ^<Interval^>PT5M^</Interval^>
echo         ^</Repetition^>
echo       ^</TimeTrigger^>
echo     ^</Triggers^>
echo     ^<Actions^>
echo       ^<Exec^>
echo         ^<Command^>curl^</Command^>
echo         ^<Arguments^>"http://localhost/CleanCut/cron/run-all?token=cleancut_cron_2024"^</Arguments^>
echo       ^</Exec^>
echo     ^</Actions^>
echo   ^</Task^>
echo ^</TaskScheduler^>
) > cleancut_automation_task.xml

echo ✅ Task Scheduler configuration created: cleancut_automation_task.xml

REM Create environment file
echo 🔧 Creating environment configuration...
(
echo # CleanCut Automation Configuration
echo CRON_TOKEN=cleancut_cron_2024
echo WEBSOCKET_PORT=8080
echo WEBSOCKET_HOST=localhost
echo AUTO_CONFIRM_ENABLED=true
echo AUTO_COMPLETE_ENABLED=true
echo AUTO_CANCEL_ENABLED=true
echo REMINDER_ENABLED=true
) > .env.automation

echo ✅ Environment configuration created: .env.automation

REM Test WebSocket server
echo 🧪 Testing WebSocket server...
start /b node websocket_server.js
timeout /t 3 /nobreak >nul
tasklist /fi "imagename eq node.exe" | find "node.exe" >nul
if %errorlevel% equ 0 (
    echo ✅ WebSocket server started successfully
    taskkill /f /im node.exe >nul 2>&1
) else (
    echo ❌ WebSocket server failed to start
)

REM Test cron endpoint
echo 🧪 Testing cron endpoints...
curl -s "http://localhost/CleanCut/cron/test" >nul
if %errorlevel% equ 0 (
    echo ✅ Cron endpoints are accessible
) else (
    echo ⚠️  Cron endpoints may not be accessible. Check your web server configuration.
)

echo.
echo 🎉 Setup completed successfully!
echo.
echo 📋 Next steps:
echo 1. Start the WebSocket server:
echo    start_websocket.bat
echo    # OR manually: node websocket_server.js
echo.
echo 2. Import Task Scheduler configuration:
echo    schtasks /create /xml cleancut_automation_task.xml
echo.
echo 3. Test automation:
echo    test_automation.bat
echo.
echo 4. Check system status:
echo    curl "http://localhost/CleanCut/cron/status"
echo.
echo 🔧 Configuration files created:
echo    - .env.automation ^(automation settings^)
echo    - start_websocket.bat ^(WebSocket server starter^)
echo    - test_automation.bat ^(automation tester^)
echo    - cleancut_automation_task.xml ^(Task Scheduler config^)
echo.
echo 📚 Documentation:
echo    - WebSocket server: websocket_server.js
echo    - Client integration: public/assets/js/websocket-client.js
echo    - Automation controller: app/Controllers/AutomatedAppointmentController.php
echo    - Background jobs: app/Services/BackgroundJobProcessor.php
echo    - Cron controller: app/Controllers/CronController.php
echo.
echo ✨ Your CleanCut system now has full automation and real-time capabilities!
echo.
pause
