# Pre-Deploy & Post-Deploy Checklist

## Before Every Deploy

- [ ] Run `git status` to verify all changes are committed
- [ ] Verify `render.yaml` has persistent disk configuration
- [ ] Run `git push` to GitHub
- [ ] Check GitHub Actions pass (if configured)
- [ ] Backup database locally: `pg_dump > backup_$(date +%Y%m%d).sql`
- [ ] Document any manual database changes

## Render Dashboard Checklist

- [ ] PostgreSQL database `kmc-db` exists and is healthy
- [ ] Web service `kmc-college` exists
- [ ] Environment variables are set correctly
- [ ] Persistent disk `kmc-storage` (10GB) is configured
- [ ] Persistent disk is mounted to:
  - `/var/www/html/storage`
  - `/var/www/html/public/uploads`

## During Deploy

Render will automatically:
1. Pull latest code from GitHub
2. Build Docker container
3. Start entrypoint script which:
   - Creates storage directories
   - Waits for PostgreSQL
   - Runs migrations (--force)
   - Seeds database
   - Creates storage symlink
   - Starts Apache

## After Deploy (Critical!)

✅ ALWAYS run these verification checks:

```bash
# 1. Check service is running
curl https://your-app.onrender.com/up

# 2. Verify storage integrity
# Open developer dashboard → Shell
ls -la storage/app/public/ | head -20
ls -la public/uploads/ | head -20

# 3. Check symlink
ls -la public/storage

# 4. Verify database
php artisan tinker
>>> Student::count()
>>> ExamScore::count()
>>> Payment::count()
>>> exit()

# 5. Run verification script (if SSH available)
bash verify-persistence.sh
```

## If Data is Missing After Deploy

1. **Check persistent disk status**
   ```bash
   df -h | grep /persistent-storage
   ```
   - Should show 10GB available
   - Should show `/var/www/html/storage` mounted

2. **Check logs**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

3. **Verify database**
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo()
   >>> DB::select('SELECT COUNT(*) FROM students')
   ```

4. **Restore from backup** (if available)
   ```bash
   psql $DATABASE_URL < backup_YYYYMMDD.sql
   ```

## Emergency Data Recovery

If data is somehow lost:

1. **From GitHub backup** (code only, not data)
2. **From PostgreSQL backup** (if created)
3. **From persistent disk** (should still exist if mounted)

To prevent future issues:
- Implement daily automated backups
- Store backup URLs in a separate cloud service
- Document all manual data changes
- Use version control for .env and configuration

## Deployment Success Indicators

✅ All green if:
- Service shows "Live" status
- `/up` endpoint responds with 200
- Dashboard loads without errors
- Student list shows data
- Photos display correctly
- Portal works for login/access

❌ Investigate if:
- Any HTTP errors in logs
- Storage directories empty
- Photos not loading
- Database queries timeout
- 403/404 errors on files
