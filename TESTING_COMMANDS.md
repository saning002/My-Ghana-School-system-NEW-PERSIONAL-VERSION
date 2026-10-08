# Testing Commands Reference

## Setup Phase (Run These First)

```bash
# 1. Navigate to project
cd C:\Users\sammy\Documents\GitHub\college-database

# 2. Seed test data (creates users + branches)
php artisan db:seed --class=TestBranchAuthSeeder

# 3. Expected output:
# Test branch authentication data created successfully!
# Super Admin: superadmin@kmc.admins / password
# HQ Admin: admin.hq@kmc.admins / password
# South Admin: admin.south@kmc.admins / password
```

---

## Manual Data Creation (If Seeder Fails)

```bash
# Start Tinker
php artisan tinker

# Create super-admin
User::create([
    'full_name' => 'Super Admin',
    'email' => 'superadmin@kmc.admins',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_super_admin' => true,
    'church_branch_id' => null,
]);

# Create branches
$hq = ChurchBranch::create([
    'name' => 'Headquarters',
    'code' => 'HQ',
    'location' => 'Downtown Campus',
]);

$south = ChurchBranch::create([
    'name' => 'South Branch',
    'code' => 'SOUTH',
    'location' => 'Southern Campus',
]);

# Create branch admins
User::create([
    'full_name' => 'HQ Administrator',
    'email' => 'admin.hq@kmc.admins',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_super_admin' => false,
    'church_branch_id' => $hq->id,
]);

User::create([
    'full_name' => 'South Administrator',
    'email' => 'admin.south@kmc.admins',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_super_admin' => false,
    'church_branch_id' => $south->id,
]);

# Type 'exit' to leave Tinker
```

---

## Verification Commands

```bash
# Check branch routes registered
php artisan route:list | findstr /I "branches"

# Check created users (with Tinker)
php artisan tinker
User::where('email', 'like', '%kmc.admins%')->orWhere('is_super_admin', true)->get();

# Check branches
ChurchBranch::all();

# Exit Tinker
exit
```

---

## Manual Testing Steps

### Test 1: Super-Admin Flow
```
1. Visit: http://localhost:8000/login
2. Click: "Super Admin Login"
3. Enter: superadmin@kmc.admins / password
4. Expected: Dashboard loads with ALL data
5. Check: "Branches" link visible in sidebar
6. Click: "Branches" → See both HQ and South
7. Click: "Manage Admins" on HQ → See admin.hq@kmc.test
```

### Test 2: Branch-Admin Flow (HQ)
```
1. Visit: http://localhost:8000/login
2. See: Branch selector with HQ and South buttons
3. Click: "Headquarters"
4. Enter: admin.hq@kmc.admins / password
5. Expected: Dashboard loads
6. Check: Statistics are HQ-only (check numbers)
7. Click: "Students" → Only HQ students shown
8. Check: "Branches" link NOT visible in sidebar
```

### Test 3: Branch-Admin Flow (South)
```
1. Visit: http://localhost:8000/login
2. Click: "South Branch"
3. Enter: admin.south@kmc.admins / password
4. Expected: Dashboard loads with South data
5. Click: "Students" → Only South students shown
6. Verify: Different data than HQ branch
```

### Test 4: Access Control (Cross-Branch Prevention)
```
# Get the South branch ID (from tinker)
php artisan tinker
ChurchBranch::where('code', 'SOUTH')->first()->id

# Let's say it's 2, visit this URL while logged in as admin.hq@kmc.test
http://localhost:8000/login/branch/2

# Try to login with admin.hq@kmc.test credentials
# Expected: 403 Forbidden error with message "Not authorized for this branch"
```

### Test 5: Create New Branch Admin (Super-Admin Only)
```
1. Login as super-admin (superadmin@kmc.test)
2. Click: "Branches" in sidebar
3. Click: "Manage Admins" button on HQ branch
4. Click: "Add Admin" button
5. Fill form:
   - Full Name: Test Admin
   - Email: testadmin@kmc.test
   - Phone: 555-1234 (optional)
   - Password: testpass123
   - Confirm: testpass123
6. Click: "Create Admin"
7. Expected: New admin appears in list
8. Now login as testadmin@kmc.test to verify
```

---

## Query Commands (For Verification)

```bash
php artisan tinker

# Count admins per branch
ChurchBranch::withCount('admins')->get();

# Get all branch admins
User::where('is_super_admin', false)->where('church_branch_id', '!=', null)->get();

# Get all super-admins
User::where('is_super_admin', true)->get();

# Get students by branch
$hq = ChurchBranch::where('code', 'HQ')->first();
$hq->students; // or Student::where('church_branch_id', $hq->id)->get();
```

---

## Start Development Server

```bash
# Method 1: Laravel serve
php artisan serve

# Method 2: With custom port
php artisan serve --port=8001

# Visit in browser: http://localhost:8000
```

---

## Database Troubleshooting

```bash
# If connection fails, check config
php artisan config:show | grep -i database

# Force reconnect with specific database
php artisan tinker --env=local

# Check current branch admins directly
php artisan tinker
User::where('is_super_admin', false)->with('branch')->get();
```

---

## Expected Test Results

| Test | Expected Result | Pass |
|------|-----------------|------|
| Super-admin sees all students | ✓ All students listed | - |
| HQ Admin sees only HQ students | ✓ Only HQ filtered | - |
| South Admin sees only South students | ✓ Only South filtered | - |
| HQ Admin tries South login URL | ✓ 403 error | - |
| Super-admin can create branch admins | ✓ New user created | - |
| Branch admin can't see Branches link | ✓ Hidden from sidebar | - |
| Cross-branch student assignment blocked | ✓ Auto-assigns own branch | - |
| Delete branch with students fails | ✓ Protected from deletion | - |

---

## Cleanup (After Testing)

```bash
# Delete test data
php artisan tinker
User::where('email', 'like', '%kmc.test%')->delete();
ChurchBranch::where('code', 'in', ['HQ', 'SOUTH'])->delete();
```

---

## Log Monitoring

```bash
# Watch logs in real-time (PowerShell)
Get-Content storage/logs/laravel.log -Wait

# Or use tail-like command
tail -f storage/logs/laravel.log

# Check for authorization errors
Select-String "ensureSuperAdmin" storage/logs/laravel.log
```

---

## Quick Browser Testing Checklist

- [ ] Visit `/login` → See branch selector
- [ ] Click branch → See branch-specific login form
- [ ] Super-admin login → Dashboard with all data
- [ ] HQ admin login → Dashboard with HQ data only
- [ ] Check sidebar → Branches link only for super-admin
- [ ] Visit `/admin/students` as HQ admin → Only HQ students
- [ ] Visit `/admin/branches` as HQ admin → 403 Forbidden
- [ ] HQ admin tries `/login/branch/2` → 403 error
- [ ] Create new branch admin → Works for super-admin
- [ ] Logout and login as new admin → Works
