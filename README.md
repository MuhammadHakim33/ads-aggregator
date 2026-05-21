# Ads Aggregator

## Initial Setup (Required)
Create the `.env` configuration file from `.env.example` and update the database settings if needed.

## Option 1: Running with Docker (Recommended)
This is the most practical method because the server and database are automatically set up within the containers.

**Prerequisites:**
- Make sure **Docker** and **Docker Compose** are installed on your computer.

**Steps:**
1. Navigate to the `docker` directory:
   ```bash
   cd docker
   ```
2. Run the containers:
   ```bash
   docker-compose up -d
   ```
3. Wait until the process is complete. The application can be accessed via browser at:
   👉 **http://localhost:8080**
4. To stop the application, run the following command (make sure you are still inside the `docker` directory):
   ```bash
   docker-compose down
   ```

---

## Option 2: Running Without Docker (Manual)
Use this option if you want to run the application directly using your local environment (XAMPP/MAMP/Native).

**Prerequisites:**
- PHP (Version 7.x)
- MySQL / MariaDB
- Composer

**Steps:**
1. **Install Dependencies:**
   Run the following command in the project's root directory to download dependencies:
   ```bash
   composer install
   ```
2. **Setup Database:**
   - Open MySQL and create a new database (e.g., named `ci3_db`).
   - Import the table structure from the `migration/tables.sql` file into the database.
   - Import the seed/dummy data from the `migration/dummy.sql` file to populate initial data.
   - Make sure the settings in the `.env` file match your local database credentials:
     ```env
     DB_HOST=localhost
     DB_NAME=ci3_db
     DB_USER=root
     DB_PASSWORD=
     ```
3. **Run the Application:**
   You can use PHP's built-in web server by running this command in the root directory:
   ```bash
   php -S localhost:8080
   ```
4. The application can be accessed via browser at:
   👉 **http://localhost:8080**

---

## Running Cron Jobs
This system requires cron jobs to periodically pull data from the APIs (Facebook, Instagram, GA4, YouTube). There are two main functions for each platform:
- `fetch_posts`: Fetch the latest posts/content.
- `sync_insights`: Fetch metrics (insights) data.

**1. When using Docker:**
Run this command in your terminal:
```bash
docker exec -it ads_aggregator_app php index.php Cron/Facebook fetch_posts
docker exec -it ads_aggregator_app php index.php Cron/Facebook sync_insights
```
*(Replace `Facebook` with `Instagram`, `Ga4`, or `Youtube` as needed).*

**2. When running without Docker (Manual):**
Run this command in the project's root directory:
```bash
php index.php Cron/Facebook fetch_posts
php index.php Cron/Facebook sync_insights
```

**3. Setup Server Crontab (Scheduled Automatically):**
To run the processes automatically in the background of a server (Linux), add the configuration to your crontab (`crontab -e`):
```bash
# Fetch posts every hour
0 * * * * cd /path/to/project/folder && php index.php Cron/Facebook fetch_posts >> /dev/null 2>&1

# Fetch insights every midnight (00:00)
0 0 * * * cd /path/to/project/folder && php index.php Cron/Facebook sync_insights >> /dev/null 2>&1
```
*(Adjust `/path/to/project/folder` to the actual directory where your project is located).*