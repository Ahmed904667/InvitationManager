#!/bin/bash

# Automated Queue Worker and Event Completion System Startup Script
# This script starts the queue worker for automated message processing AND the event completion scheduler

echo "🚀 Starting Automated System for Guest Manager..."
echo "This will continuously process:"
echo "  • Scheduled events and reminders (Queue Worker)"
echo "  • Event auto-completion (Scheduler)"
echo "Press Ctrl+C to stop all processes."
echo ""

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo "❌ Error: Laravel artisan file not found. Please run this script from your Laravel project root."
    exit 1
fi

# Check if queue driver is configured
QUEUE_DRIVER=$(php artisan tinker --execute="echo config('queue.default');" 2>/dev/null)
if [ "$QUEUE_DRIVER" = "sync" ]; then
    echo "⚠️  Warning: Queue driver is set to 'sync'. For automated processing, consider using 'database' or 'redis'."
    echo "   Update your .env file: QUEUE_CONNECTION=database"
    echo ""
fi

# Function to handle cleanup on script exit
cleanup() {
    echo ""
    echo "🛑 Stopping all automated processes..."
    
    # Kill background processes
    if [ ! -z "$QUEUE_PID" ]; then
        echo "   Stopping queue worker (PID: $QUEUE_PID)..."
        kill $QUEUE_PID 2>/dev/null
    fi
    
    if [ ! -z "$SCHEDULER_PID" ]; then
        echo "   Stopping scheduler (PID: $SCHEDULER_PID)..."
        kill $SCHEDULER_PID 2>/dev/null
    fi
    
    echo "✅ All processes stopped."
    exit 0
}

# Set up signal handlers
trap cleanup SIGINT SIGTERM

# Start the queue worker in background
echo "📅 Starting queue worker for automated scheduled message processing..."
php artisan queue:work \
    --sleep=3 \
    --tries=3 \
    --timeout=60 \
    --memory=128 \
    --verbose &
QUEUE_PID=$!

echo "✅ Queue worker started (PID: $QUEUE_PID)"

# Start the scheduler in background using Laravel's built-in schedule:work
echo "⏰ Starting event auto-completion scheduler..."
echo "   This will check for completed events every 5 minutes"
echo ""

# Start Laravel's built-in schedule:work command
php artisan schedule:work &
SCHEDULER_PID=$!

echo "✅ Scheduler started (PID: $SCHEDULER_PID)"
echo ""
echo "🎉 Automated system is now running!"
echo "   • Queue Worker: Processing scheduled messages"
echo "   • Scheduler: Auto-completing events every 5 minutes"
echo "   • Model Observer: Real-time event completion"
echo ""
echo "📊 Monitor logs with: tail -f storage/logs/laravel.log"
echo "🛑 Press Ctrl+C to stop all processes"
echo ""

# Wait for background processes
wait
