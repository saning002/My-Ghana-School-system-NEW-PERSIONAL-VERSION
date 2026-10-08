<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><style>
body { font-family:Arial,sans-serif; font-size:14px; color:#1e293b; background:#f8fafc; margin:0; padding:0; }
.wrapper { max-width:540px; margin:30px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
.header { background:linear-gradient(135deg,#1a1a2e,#0f3460); padding:24px 28px; color:#fff; }
.header h2 { margin:0; font-size:18px; }
.header p  { margin:4px 0 0; opacity:0.7; font-size:12px; }
.body { padding:24px 28px; }
.body p { line-height:1.7; color:#475569; }
.detail-box { background:#f8fafc; border-radius:8px; padding:14px 18px; margin:18px 0; }
.detail-box table { width:100%; border-collapse:collapse; }
.detail-box td { padding:5px 0; font-size:13px; }
.detail-box td:first-child { color:#94a3b8; width:45%; }
.detail-box td:last-child { font-weight:600; }
.warning { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 16px; font-size:12px; color:#92400e; margin:16px 0; }
.footer { background:#f8fafc; padding:16px 28px; text-align:center; font-size:11px; color:#94a3b8; border-top:1px solid #e2e8f0; }
</style></head>
<body>
<div class="wrapper">
    <div class="header">
        <h2><i>&#128190;</i> Database Backup</h2>
        <p>{{ $tenant->name }} · {{ now()->format('d F Y') }}</p>
    </div>
    <div class="body">
        <p>Dear <strong>{{ $tenant->admin_name }}</strong>,</p>
        <p>A database backup for <strong>{{ $tenant->name }}</strong> has been prepared and is attached to this email.</p>

        <div class="detail-box">
            <table>
                <tr><td>School</td><td>{{ $tenant->name }}</td></tr>
                <tr><td>Backup File</td><td>{{ $backup->filename }}</td></tr>
                <tr><td>File Size</td><td>{{ $backup->file_size_human }}</td></tr>
                <tr><td>Backup Date</td><td>{{ $backup->created_at->format('d M Y H:i') }}</td></tr>
                <tr><td>Trigger</td><td>{{ ucfirst($backup->trigger) }}</td></tr>
            </table>
        </div>

        @if($large_file ?? false)
        <div class="warning">
            &#9888; The backup file is too large to attach directly. Please contact your system administrator to download it from the owner panel.
        </div>
        @else
        <p>The backup file (<code>.sql.gz</code>) is attached. To restore, decompress with <code>gunzip</code> and import using <code>mysql</code>.</p>
        @endif

        <div class="warning">
            &#128274; Keep this backup file secure. It contains your complete school database.
        </div>
    </div>
    <div class="footer">&copy; {{ date('Y') }} School Management Platform</div>
</div>
</body>
</html>
