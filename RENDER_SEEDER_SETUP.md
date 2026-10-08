# Running Seeder on Render Deployment

## Option A: Using Render Dashboard (Easiest)

1. Go to your Render service dashboard: https://dashboard.render.com
2. Find your college-database service
3. Click on the **"Shell"** tab at the top
4. Run this command:
```bash
php artisan db:seed --class=TestBranchAuthSeeder
```
5. When prompted "Are you sure you want to run this command? (yes/no)", type: `yes`
6. Wait for completion - you should see:
   ```
   Test branch authentication data created successfully!
   Super Admin: superadmin@kmc.test / password
   HQ Admin: admin.hq@kmc.test / password
   South Admin: admin.south@kmc.test / password
   ```

---

## Option B: Using SSH (If Enabled)

1. Get your SSH key ready
2. SSH into your Render service:
```bash
ssh [your-service-name]@[your-render-service-url]
```
3. Run the seeder:
```bash
php artisan db:seed --class=TestBranchAuthSeeder
```
4. Type `yes` when prompted

---

## Option C: Using Render CLI

1. Install Render CLI (if not already installed):
```bash
npm install -g render-cli
# or
pip install render-cli
```

2. Authenticate:
```bash
render login
```

3. Run the seeder:
```bash
render exec -s college-database "php artisan db:seed --class=TestBranchAuthSeeder"
```

---

## Expected Output

When successful, you should see:
```
   INFO  Seeding database.

Test branch authentication data created successfully!
Super Admin: superadmin@kmc.test / password
HQ Admin: admin.hq@kmc.test / password
South Admin: admin.south@kmc.test / password
```

---

## Then Test Credentials

Go to your deployed app's login page (e.g., https://college-database.onrender.com/login)

1. **Super Admin Login:**
   - Click "Super Admin Login"
   - Email: `superadmin@kmc.test`
   - Password: `password`

2. **Branch Admin Login:**
   - Click "Headquarters" (or other branch)
   - Email: `admin.hq@kmc.test`
   - Password: `password`

---

## Troubleshooting

**Issue:** "Command not found: php"
- **Fix:** Make sure you're in the app directory: `cd /opt/render/project/src` or similar

**Issue:** "SQLSTATE error"
- **Fix:** Check database is running and credentials are correct in `.env`

**Issue:** Database connection fails
- **Fix:** Ensure `DATABASE_URL` env var is set correctly in Render dashboard Settings

---

## Manual Alternative (If Seeder Fails)

If the seeder fails, manually create users via SSH:
```bash
php artisan tinker
```

Then paste:
```php
use App\Models\User;
use App\Models\ChurchBranch;
use Illuminate\Support\Facades\Hash;

// Create HQ branch
$hq = ChurchBranch::create([
    'name' => 'Headquarters',
    'code' => 'HQ',
    'location' => 'Downtown Campus',
]);

// Create South branch
$south = ChurchBranch::create([
    'name' => 'South Branch',
    'code' => 'SOUTH',
    'location' => 'Southern Campus',
]);

// Create super-admin
User::create([
    'full_name' => 'Super Admin',
    'email' => 'superadmin@kmc.test',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_super_admin' => true,
    'church_branch_id' => null,
]);

// Create HQ admin
User::create([
    'full_name' => 'HQ Administrator',
    'email' => 'admin.hq@kmc.test',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_super_admin' => false,
    'church_branch_id' => $hq->id,
]);

// Create South admin
User::create([
    'full_name' => 'South Administrator',
    'email' => 'admin.south@kmc.test',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_super_admin' => false,
    'church_branch_id' => $south->id,
]);

exit
```

---

Done! Users will now be created on your live server.
