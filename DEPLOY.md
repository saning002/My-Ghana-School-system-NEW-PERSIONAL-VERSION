# Deploying to Render

## ⚠️ CRITICAL: Data Persistence Configuration

**Before deploying, ensure your `render.yaml` includes persistent disk configuration for storage.**

The `render.yaml` in this project automatically configures:
- ✅ PostgreSQL database (persistent)
- ✅ Persistent disk `kmc-storage` (10GB) for student files
- ✅ Mounted at `/var/www/html/storage` and `/var/www/html/public/uploads`

**Your data will NOT be lost on deploy if this is configured.**

---

## Prerequisites
- A [Render](https://render.com) account
- Your project pushed to a GitHub or GitLab repository
- Docker support enabled on Render (included in free tier)

---

## Step 1 — Push to GitHub

```bash
git init
git add .
git commit -m "Initial commit — KMC Enterprise"
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
git push -u origin main
```

---

## Step 2 — Deploy via `render.yaml`

This project uses `render.yaml` for automatic configuration:

1. Go to [render.com/dashboard](https://dashboard.render.com)
2. Click **New → Blueprint**
3. Connect your GitHub repository
4. Select the `render.yaml` file from this project
5. Click **Deploy**

Render will automatically:
- ✅ Create PostgreSQL database
- ✅ Create persistent disk for storage
- ✅ Deploy Docker container
- ✅ Run migrations
- ✅ Create storage symlinks
- ✅ Seed data

---

## Step 3 — Verify Data After Deploy

After deployment, verify your data persisted:

```bash
# SSH into your Render service
# Run verification script
bash verify-persistence.sh
```

This checks:
- ✅ Storage directories exist
- ✅ Student photos are present
- ✅ Database contains records
- ✅ File permissions are correct
- ✅ Storage symlink is valid

---

## Manual Database & Backup Management
|-----|-------|
| `APP_NAME` | Kingdom Ministerial University College |
| `APP_ENV` | production |
| `APP_DEBUG` | false |
| `APP_KEY` | *(click Generate)* |
| `APP_URL` | https://your-app.onrender.com |
| `DB_CONNECTION` | pgsql |
| `DB_HOST` | *(from your Render DB → Internal Host)* |
| `DB_PORT` | 5432 |
| `DB_DATABASE` | *(from your Render DB → Database)* |
| `DB_USERNAME` | *(from your Render DB → Username)* |
| `DB_PASSWORD` | *(from your Render DB → Password)* |
| `SESSION_DRIVER` | database |
| `CACHE_DRIVER` | database |
| `LOG_CHANNEL` | stderr |
| `LOG_LEVEL` | error |

> **Tip:** You can also use `render.yaml` (already in the project root) to configure everything as Infrastructure as Code.

---

## Step 5 — Deploy

Click **Deploy** — Render will:
1. Install Composer dependencies
2. Cache config, routes, and views
3. Run all database migrations
4. Run the database seeder (creates default programs and admin user)
5. Start the PHP server

---

## After Deployment

- Visit your app URL: `https://kmc-college.onrender.com`
- Log in with the seeded admin credentials (check `database/seeders/DatabaseSeeder.php`)
- The logo at `public/images/logo.png` is committed to the repo and will be served directly

---

## Notes

- **Free tier** on Render spins down after 15 minutes of inactivity — first request after sleep takes ~30 seconds
- **Storage:** Uploaded files are ephemeral on Render's free tier. For persistent file storage, configure an S3-compatible bucket and set `FILESYSTEM_DISK=s3`
- **Sessions:** Using `database` driver — sessions are stored in PostgreSQL and survive restarts
