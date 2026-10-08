Render Blueprint deployment checklist

1) Connect repository
- Sign in to https://render.com and connect your GitHub account.
- Create -> "Blueprint" -> select the repository `college-database-converted-into-Ghana-School-System`.
- Choose branch: `main`.

2) Confirm service configuration (from `render.yaml`)
- Service type: `web`, runtime: `docker`, plan: `free`.
- Dockerfile path: `./Dockerfile` (already in repo).
- Root dir: `.`
- Build command: `./render-build.sh` (already set in `render.yaml`).
- Health check path: `/up` (ensure your app returns 200 at this route).
- Disk: persistent mount configured in `render.yaml` at `/var/www/html/storage/app/public`.

3) Database (Blueprint will create it)
- Name: `kmc-db` (as in `render.yaml`).
- Plan: `free` (note free DB has storage and connection limits).
- `render.yaml` pulls DB_HOST/port/database/user/password into the service automatically.

4) Required environment variables to set in the Render Blueprint UI
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` = leave to Render (`generateValue: true`) or provide one
- `DB_CONNECTION=pgsql` (already in `render.yaml`)
- `SESSION_DRIVER=database`
- `CACHE_DRIVER=database`
- `QUEUE_CONNECTION=sync`
- `LOG_CHANNEL=stderr`
- `LOG_LEVEL=error`
- `FILESYSTEM_DISK=local` (default) or set to `cloudinary` if using Cloudinary filesystem

Mail (set real SMTP in production or use Mailgun/SendGrid)
- `MAIL_MAILER=smtp`
- `MAIL_HOST=smtp.example.com`
- `MAIL_PORT=587`
- `MAIL_USERNAME=`
- `MAIL_PASSWORD=`
- `MAIL_ENCRYPTION=tls`
- `MAIL_FROM_ADDRESS=admin@kmc.edu`
- `MAIL_FROM_NAME="Kingdom Ministerial University College"`

Cloudinary (optional but recommended for persistent student photos)
- `CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME`
- `CLOUDINARY_CLOUD_NAME=`
- `CLOUDINARY_API_KEY=`
- `CLOUDINARY_API_SECRET=`

Other optional vars
- `APP_URL` — Render sets `RENDER_EXTERNAL_URL`, the container entrypoint maps it to `APP_URL` automatically.

5) Launching the Blueprint
- Review the auto-created service and database in Render's UI.
- Click Deploy. Render will run the build step (`./render-build.sh`) which installs dependencies, caches config, and runs migrations.
- The container entrypoint will run first-deploy seeding (creates admin user if missing).

6) After deploy checks
- Open the app URL from Render dashboard.
- Check service logs for any errors: Deploy logs and Live logs.
- Visit `https://<your-app>/up` to verify health.
- If you enabled Cloudinary, check a student photo upload path to ensure files persist.
- Test login as seeded admin (Seeder `TestBranchAuthSeeder` creates test credentials — check seeders for exact values).

7) Troubleshooting notes
- Free Postgres: watch for connection limits and storage caps.
- If migrations fail due to DB not ready during build, consider leaving migrations to the entrypoint (already done in `docker-entrypoint.sh`).
- If uploads fail, verify the disk mount and that `storage/app/public` is writable.

8) Local sanity commands
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

If you'd like, I can:
- Walk you through the exact Render UI clicks step-by-step while you have Render open, or
- Produce the exact env value suggestions (with placeholders) you can copy-paste into the Blueprint UI.
