# Ads Aggregator

## Initial Setup (Required)
Create the `.env` configuration file from `.env.example`. Make sure your database settings are configured to connect to your local MySQL instance (port 3306):

```env
DB_HOST=127.0.0.1
DB_NAME=aggregator
DB_USER=user
DB_PASSWORD=pass
DB_ROOT_PASSWORD=root
```

## Running the Application

This project uses Docker to run the database (MySQL), while the application code runs natively using PHP and Nginx/Apache on your host machine (WSL/Linux).

**Prerequisites:**
- PHP (Version 7.x)
- Composer
- Docker & Docker Compose (Optional)
- Webserver

**Steps:**

1. **Install PHP Dependencies:**
   Run the following command to install the required libraries:
   ```bash
   composer install
   ```

2. **Database Migration & Seeding:**
   (If you haven't already setup the database)
   - Import the table structure from `migration/tables.sql` into the database.
   - Import the dummy data from `migration/dummy.sql`.

3. **Access the Application:**
   Access the app through your local web server (e.g., Nginx) at the configured domain or port.
   👉 **http://localhost:8080** (or your configured port)

---

## Running Cron Jobs
This system requires cron jobs to periodically pull data from the APIs (Facebook, Instagram, GA4, YouTube). There are two main functions for each platform:
- `fetch`: Fetch the latest posts/content.
- `sync`: Fetch metrics (insights) data.

**1. Manual Run:**
Run this command in the project's root directory:
```bash
# To run for a specific platform (e.g., facebook, instagram, youtube, ga4):
php index.php Cron/Platform fetch facebook
php index.php Cron/Platform sync facebook

# To run for ALL platforms at once:
php index.php Cron/Platform fetch all
php index.php Cron/Platform sync all

# To specify a custom time frame (format: YYYY-MM-DD):
# Usage: php index.php Cron/Platform [action] [platform] [since] [until]
php index.php Cron/Platform fetch all 2023-01-01 2023-12-31
php index.php Cron/Platform sync facebook 2023-10-01 2023-10-31
```

**2. Setup Server Crontab (Scheduled Automatically):**
To run the processes automatically in the background of a server (Linux), add the configuration to your crontab (`crontab -e`):
```bash
# Fetch posts for all platforms every hour
# Logs are appended to logs/cron_fetch.log
0 * * * * cd /path/to/project && php index.php Cron/Platform fetch all >> logs/cron_fetch.log 2>&1

# Sync insights for all platforms every midnight (00:00)
# Logs are appended to logs/cron_sync.log
0 0 * * * cd /path/to/project && php index.php Cron/Platform sync all >> logs/cron_sync.log 2>&1
```
*(Adjust `/path/to/project` to the actual directory where your project is located).*

**Log Files:**
| File | Description |
|---|---|
| `logs/cron_fetch.log` | Output log from the fetch cron job |
| `logs/cron_sync.log` | Output log from the sync cron job |

> **Tip:** To rotate logs and prevent the log files from growing too large, add `logrotate` configuration or clear manually.
