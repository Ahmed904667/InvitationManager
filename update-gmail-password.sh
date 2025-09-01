#!/bin/bash

echo "🔧 Gmail Password Update Script"
echo "================================"
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    exit 1
fi

echo "📋 Current Gmail configuration:"
echo "================================"
grep -E "MAIL_|SMTP_" .env
echo ""

echo "🔑 Please enter your new Gmail app password (16 characters):"
read -s new_password

if [ -z "$new_password" ]; then
    echo "❌ Error: Password cannot be empty!"
    exit 1
fi

# Update the password in .env file
sed -i '' "s/MAIL_PASSWORD=.*/MAIL_PASSWORD=\"$new_password\"/" .env

echo ""
echo "✅ Password updated successfully!"
echo ""

echo "📋 Updated Gmail configuration:"
echo "================================"
grep -E "MAIL_|SMTP_" .env
echo ""

echo "🔄 Clearing configuration cache..."
php artisan config:clear

echo ""
echo "✅ Configuration updated! You can now test your email."
echo "🌐 Visit: http://localhost:8001/test/smtp-diagnostic.php"





