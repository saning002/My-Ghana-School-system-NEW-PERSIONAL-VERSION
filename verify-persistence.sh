#!/bin/bash
# Deployment verification script - run after every deploy

echo "=== Data Persistence Verification ==="
echo ""

# Check if we're in the Laravel root
if [ ! -f "artisan" ]; then
    echo "❌ Not in Laravel root directory. Run from project root."
    exit 1
fi

echo "✓ Running from Laravel root"
echo ""

# Check persistent storage paths
echo "Checking persistent storage paths..."
if [ -d "/persistent-storage" ]; then
    echo "✓ Persistent storage mounted at /persistent-storage"

    if [ -d "/persistent-storage/storage" ]; then
        echo "✓ /persistent-storage/storage exists"
        PERSISTENT_STORAGE_COUNT=$(find /persistent-storage/storage -type f 2>/dev/null | wc -l)
        echo "  - Persistent storage files: $PERSISTENT_STORAGE_COUNT files"
    else
        echo "❌ /persistent-storage/storage missing!"
        exit 1
    fi

    if [ -d "/persistent-storage/uploads" ]; then
        echo "✓ /persistent-storage/uploads exists"
        PERSISTENT_UPLOADS_COUNT=$(find /persistent-storage/uploads -type f 2>/dev/null | wc -l)
        echo "  - Persistent uploads files: $PERSISTENT_UPLOADS_COUNT files"
    else
        echo "❌ /persistent-storage/uploads missing!"
        exit 1
    fi
else
    echo "⚠ Persistent storage not mounted (local development?)"
fi

# Check local storage paths (for compatibility)
if [ -d "storage/app/public" ]; then
    echo "✓ storage/app/public exists"
    PHOTO_COUNT=$(find storage/app/public -type f 2>/dev/null | wc -l)
    echo "  - Student photos: $PHOTO_COUNT files"
else
    echo "⚠ storage/app/public missing (using persistent storage)"
fi

if [ -d "public/uploads" ]; then
    echo "✓ public/uploads exists (fallback)"
    FALLBACK_COUNT=$(find public/uploads -type f 2>/dev/null | wc -l)
    echo "  - Fallback uploads: $FALLBACK_COUNT files"
else
    echo "⚠ public/uploads not found"
fi

echo ""

# Check storage symlink
echo "Checking storage symlink..."
if [ -L "public/storage" ]; then
    echo "✓ Storage symlink exists"
    TARGET=$(readlink public/storage)
    echo "  - Points to: $TARGET"

    # Check if it points to persistent storage
    if [[ "$TARGET" == *"/persistent-storage"* ]]; then
        echo "✓ Symlink points to persistent storage"
    else
        echo "⚠ Symlink points to local storage (not persistent)"
    fi
else
    echo "⚠ Storage symlink missing! Creating..."
    if [ -d "/persistent-storage/storage/app/public" ]; then
        ln -s /persistent-storage/storage/app/public public/storage
        echo "✓ Symlink created (persistent storage)"
    else
        php artisan storage:link || ln -s ../storage/app/public public/storage
        echo "✓ Symlink created (local storage)"
    fi
fi

# Check uploads symlink
echo "Checking uploads symlink..."
if [ -L "public/uploads" ]; then
    echo "✓ Uploads symlink exists"
    UPLOAD_TARGET=$(readlink public/uploads)
    echo "  - Points to: $UPLOAD_TARGET"

    # Check if it points to persistent storage
    if [[ "$UPLOAD_TARGET" == *"/persistent-storage"* ]]; then
        echo "✓ Uploads symlink points to persistent storage"
    else
        echo "⚠ Uploads symlink points to local storage (not persistent)"
    fi
else
    echo "⚠ Uploads symlink missing!"
    if [ -d "/persistent-storage/uploads" ]; then
        ln -s /persistent-storage/uploads public/uploads
        echo "✓ Uploads symlink created (persistent storage)"
    fi
fi

echo ""

# Check database
echo "Checking database..."
php artisan tinker --execute "
    echo 'Database Connection: ' . config('database.default') . PHP_EOL;
    echo 'Students in DB: ' . App\Models\Student::count() . PHP_EOL;
    echo 'Users in DB: ' . App\Models\User::count() . PHP_EOL;
    echo 'Programs in DB: ' . App\Models\Program::count() . PHP_EOL;
" 2>/dev/null || echo "❌ Database check failed"

echo ""

# Check file permissions
echo "Checking file permissions..."
STORAGE_PERM=$(stat -c "%a" storage 2>/dev/null || stat -f "%OLp" storage | sed 's/.*\(.\{3\}\)$/\1/')
if [[ "$STORAGE_PERM" == *"7"* ]] || [[ "$STORAGE_PERM" == *"5"* ]]; then
    echo "✓ Storage permissions OK ($STORAGE_PERM)"
else
    echo "⚠ Storage permissions may be restrictive ($STORAGE_PERM)"
fi

echo ""
echo "=== Verification Complete ==="
echo ""
echo "If all checks passed, your data should be safe after deploy!"
