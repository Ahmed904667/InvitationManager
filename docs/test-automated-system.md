# Test Automated Scheduled Messages System

## 🧪 **Testing the Automated System**

### **Step 1: Start the Automated Worker**

Open a new terminal and run:
```bash
# Start the automated queue worker
./start-automated-worker.sh
```

You should see:
```
🚀 Starting Automated Queue Worker for Scheduled Messages...
This will continuously process scheduled events and reminders automatically.
Press Ctrl+C to stop the worker.

📅 Starting queue worker for automated scheduled message processing...
```

### **Step 2: Create a Test Scheduled Event**

1. Go to your Laravel application
2. Create a new event
3. Set the scheduled time to **2-3 minutes in the future**
4. Complete the event creation

### **Step 3: Watch the Automation**

The queue worker will automatically:
- ✅ **Detect** the scheduled event
- ✅ **Wait** until the scheduled time arrives
- ✅ **Process** the event automatically
- ✅ **Send** the invitations
- ✅ **Update** the event status to "sent"

### **Step 4: Verify Results**

Check the logs:
```bash
tail -f storage/logs/laravel.log
```

You should see logs like:
```
[2025-08-23 14:44:48] local.INFO: 🔄 [SCHEDULED_EVENT] Starting to send scheduled event invitations
[2025-08-23 14:44:52] local.INFO: ✅ [SCHEDULED_EVENT] Successfully sent scheduled event invitations
```

## 🎯 **What Happens Automatically:**

### **When You Create a Scheduled Event:**
1. **Event is created** with status "scheduled"
2. **Job is dispatched** with delay to scheduled time
3. **Queue worker waits** for the scheduled time
4. **At scheduled time**, job automatically executes
5. **Invitations are sent** to all guests
6. **Event status changes** to "sent"

### **When You Create a Scheduled Reminder:**
1. **Reminder is created** with status "pending"
2. **Job is dispatched** with delay to scheduled time
3. **Queue worker waits** for the scheduled time
4. **At scheduled time**, job automatically executes
5. **Reminder is sent** via email/WhatsApp
6. **Reminder status changes** to "sent"

## 🔍 **Monitoring the System:**

### **Check Queue Status:**
```bash
# Check if jobs are in queue
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# View queue statistics
php artisan queue:stats
```

### **Check Scheduled Messages:**
```bash
# View scheduled events
php artisan events:process-scheduled stats

# View all scheduled messages
php artisan messages:process-scheduled --force
```

### **Web Dashboard:**
Visit `/organizer/scheduled-messages` to see real-time status.

## 🚨 **Troubleshooting:**

### **If messages aren't being sent automatically:**

1. **Check if queue worker is running:**
```bash
ps aux | grep "queue:work"
```

2. **Check queue driver:**
```bash
php artisan tinker --execute="echo config('queue.default');"
```

3. **Check for failed jobs:**
```bash
php artisan queue:failed
```

4. **Restart the queue worker:**
```bash
# Stop current worker (Ctrl+C)
# Then restart:
./start-automated-worker.sh
```

### **If jobs are failing:**

1. **Check logs:**
```bash
tail -f storage/logs/laravel.log
```

2. **Retry failed jobs:**
```bash
php artisan queue:retry all
```

3. **Check configuration:**
- Mail settings in `.env`
- SMS/WhatsApp settings
- Database connection

## ✅ **Success Indicators:**

- ✅ Queue worker is running continuously
- ✅ Scheduled events are processed automatically
- ✅ Event status changes from "scheduled" to "sent"
- ✅ Invitations are created in the database
- ✅ No manual intervention required
- ✅ Logs show successful processing

## 🎉 **You're Done!**

Once the queue worker is running, your system will:
- **Automatically send** scheduled events at their scheduled time
- **Automatically send** scheduled reminders at their scheduled time
- **Handle retries** if sending fails
- **Require zero manual intervention**

The system is now **fully automated**! 🚀
