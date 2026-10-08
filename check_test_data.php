<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::select('id', 'email', 'full_name', 'is_super_admin', 'church_branch_id')
    ->where('email', 'like', '%kmc.admins%')
    ->orWhere('is_super_admin', true)
    ->get();

echo "Test Users Created:\n";
echo str_repeat("-", 80) . "\n";
foreach ($users as $user) {
    $branch = $user->church_branch_id ? " (Branch ID: {$user->church_branch_id})" : " (No Branch - Super Admin)";
    echo "• {$user->full_name} ({$user->email})" . $branch . "\n";
}
echo str_repeat("-", 80) . "\n";

$branches = \App\Models\ChurchBranch::select('id', 'name', 'code')->get();
echo "\nBranches Created:\n";
echo str_repeat("-", 80) . "\n";
foreach ($branches as $branch) {
    echo "• {$branch->name} (Code: {$branch->code}, ID: {$branch->id})\n";
}
echo str_repeat("-", 80) . "\n";
