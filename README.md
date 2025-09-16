# Guest Manager Laravel - System Evaluation


This README provides quick setup instructions for the Guest Manager Laravel system. The system is a comprehensive event guest management platform with automated features, multi-role access, and real-time notifications.

## ⚡ Quick Start (5 minutes)

### 1. Prerequisites Check
Ensure you have:
- **PHP 8.2+** with required extensions
- **Composer** installed
- **Node.js 18+** and **npm**


### 2. Install
```bash

# Install dependencies
composer install
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Create SQLite database (default)
touch database/database.sqlite

# Run migrations and seed data
php artisan migrate --seed
```

### 5. Build and Run
```bash
# Build frontend assets
npm run build

# Start the application
php artisan serve
```

**Access the system at:** `http://localhost:8000`

## 🔑 Test Accounts

The system comes with pre-seeded test accounts:


### Organizer Account
- **URL:** `http://localhost:8000/organizer`
- **Email:** `organizer@example.com`
- **Password:** `password`
- **Access:** Create events, manage guest lists, send invitations



### 4. Automated Features Test
```bash
# Start the automated system (in a separate terminal)
./start-automated-worker.sh
```

This enables:
- **Scheduled message processing**
- **Event auto-completion**
- **Real-time notifications**


## 🔧 Configuration Notes

### Environment Variables
The system works out-of-the-box with SQLite, but for full SMS functionality:

```env
# Add to .env file
TWILIO_SID=your-twilio-sid
TWILIO_AUTH_TOKEN=your-twilio-token
TWILIO_PHONE_NUMBER=+1234567890

# For email functionality
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### Database
- **Default:** SQLite (`database/database.sqlite`)
- **Alternative:** MySQL/PostgreSQL (update `.env` file)

## 📁 Project Structure

```
app/
├── Organizer/          # Event management features
├── Jobs/              # Background job processing
├── Services/          # Business logic
└── Shared/Models/     # Database models

resources/
├── views/             # Blade templates
├── css/               # Tailwind CSS
└── js/                # JavaScript components

database/
├── migrations/        # Database schema
└── seeders/          # Test data
```

## 🚀 Advanced Testing

### Test Automated System
```bash
# Start all automated processes
composer run dev
```

This runs:
- Laravel development server
- Queue worker for background jobs
- Vite dev server for frontend
- Log monitoring

### Test Queue Processing
```bash
# Process queued jobs manually
php artisan queue:work --once

# View queue status
php artisan queue:monitor
```

### Test Database Operations
```bash
# Reset database with fresh data
php artisan migrate:fresh --seed

# View database content
php artisan tinker
# Then: User::all(), Event::all(), etc.
```

