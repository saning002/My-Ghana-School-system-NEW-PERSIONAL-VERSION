# Data Protection & Persistence Configuration

## Critical Files
- `.env` - DO NOT LOSE
- `storage/app/public/` - Student photos (PERSISTENT DISK)
- `public/uploads/` - Fallback uploads (PERSISTENT DISK)
- Database (PostgreSQL - PERSISTENT)

## Persistent Storage Locations on Render

### Mount Points:
1. `/var/www/html/storage` → Render disk `kmc-storage`
2. `/var/www/html/public/uploads` → Render disk `kmc-storage`

### Auto-created Directories:
- `/var/www/html/storage/app/public/students/photos`
- `/var/www/html/storage/app/public/students/backgrounds`
- `/var/www/html/public/uploads/students/photos`
- `/var/www/html/public/uploads/students/backgrounds`

## Database Backups

PostgreSQL on Render is automatically backed up. To manually backup:

```bash
# Backup database
pg_dump $DATABASE_URL > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore from backup
psql $DATABASE_URL < backup_YYYYMMDD_HHMMSS.sql
```

## Data Validation on Deploy

The entrypoint script now:
1. ✅ Verifies persistent storage paths exist
2. ✅ Creates storage symlinks properly
3. ✅ Ensures correct file permissions
4. ✅ Runs migrations safely (--force)
5. ✅ Seeds data only if needed

## Checking Data After Deploy

```bash
# Check storage structure
ls -la storage/app/public/
ls -la public/uploads/
php artisan tinker
>>> Student::count() // Verify students exist
>>> Student::first()->photo // Verify photo path exists
```

## Environment Variables

Set in Render dashboard:
- `APP_KEY` - Auto-generated
- `SESSION_DRIVER=database` - Sessions stored in DB
- `CACHE_DRIVER=database` - Cache stored in DB
- `QUEUE_CONNECTION=sync` - Synchronous queue

## DO NOT:
- ❌ Manually delete `storage/` or `public/uploads/`
- ❌ Run migrations with `--fresh` in production
- ❌ Change database credentials without backup
- ❌ Deploy without verifying persistent disk is mounted

## Verification After Deploy

```bash
# SSH into Render service
# Check persistent disk mount
df -h | grep /persistent-storage

# Check storage symlink
ls -la public/storage

# Verify file count hasn't changed
find storage/app/public -type f | wc -l
find public/uploads -type f | wc -l
```
