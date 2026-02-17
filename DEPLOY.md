# Deployment Guide for InvitationManager

This guide covers how to deploy your Laravel application to **Railway** or **Render**, which are excellent "simple hosting" solutions that handle the server setup for you.

## Option 1: Railway (Recommended)
Railway is very easy to use and automatically detects Laravel.

1.  **Sign Up**: Go to [railway.app](https://railway.app/) and sign up with GitHub.
2.  **New Project**: Click "New Project" -> "Deploy from GitHub repo" -> Select `InvitationManager`.
3.  **Add Database**:
    *   In your project view, click "New" -> "Database" -> "MySQL".
    *   This will create a managed MySQL instance for you.
4.  **Configure Environment Variables**:
    *   Click on your specific *Laravel service* card.
    *   Go to the "Variables" tab.
    *   Add the following variables (you can find database details in the MySQL service "Connect" tab):
        *   `APP_NAME`: `InvitationManager`
        *   `APP_ENV`: `production`
        *   `APP_DEBUG`: `false`
        *   `APP_URL`: `https://<your-railway-url>.up.railway.app` (you get this after generation)
        *   `APP_KEY`: Generate one locally with `php artisan key:generate --show` and paste it here.
        *   `DB_CONNECTION`: `mysql`
        *   `DB_HOST`: `junction.proxy.rlwy.net` (Example, copy from MySQL service)
        *   `DB_PORT`: `3306` (Copy from MySQL service)
        *   `DB_DATABASE`: `railway` (Copy from MySQL service)
        *   `DB_USERNAME`: `root` (Copy from MySQL service)
        *   `DB_PASSWORD`: (Copy from MySQL service)
5.  **Build & Deploy**: Railway effectively handles the build script (installing composer dependencies and NodeJS assets).
6.  **Run Migrations**:
    *   Once deployed, go to the "Settings" tab of your service.
    *   Under "Deploy" -> "Start Command", you might want to force migrations on deploy:
        `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`
        *(Note: Ideally use Nginx/Apache in production, but `artisan serve` works for simple low-traffic apps)*

## Option 2: Render
Render is another great option with a free tier for web services.

1.  **Sign Up**: Go to [render.com](https://render.com/).
2.  **New Web Service**: Click "New" -> "Web Service" -> Connect your GitHub repo.
3.  **Settings**:
    *   **Environment**: `Docker` (or let it auto-detect PHP). If using Docker, you'll need a Dockerfile.
    *   **Build Command**: `composer install --no-dev --optimize-autoloader && npm install && npm run build`
    *   **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
4.  **Environment Variables**:
    *   Add the same variables as above (`APP_KEY`, `DB_...`).
    *   **Database**: You will need to create a "PostgreSQL" service in Render (free tier available) and link it.
        *   Update `DB_CONNECTION` to `pgsql`.
        *   Update `DB_PORT` to `5432`.

## Important Notes
*   **APP_KEY**: Crucial for security. Never commit this to Git. Always set it in the hosting dashboard.
*   **Database**: SQLite (the default in your project) will **reset** every time you deploy on these platforms because the file system is "ephemeral". You **MUST** use a managed MySQL or PostgreSQL database (provided by Railway/Render) for data to persist.
*   **Migrations**: The internal database migration scripts have been updated to be robust against re-runs and partial failures. Ensure you are deploying the latest version.
