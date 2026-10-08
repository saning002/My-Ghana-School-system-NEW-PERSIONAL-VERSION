<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

Illuminate\Support\Facades\Facade::setFacadeApplication($app);

$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$app->instance(Illuminate\Http\Request::class, $request);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = new App\Models\User([
    'full_name' => 'Test Admin',
    'email' => 'admin@test.com',
    'role' => 'admin',
    'is_super_admin' => false,
]);

auth()->setUser($user);

$view = view('admin.settings.profile', [
    'gradingPreset' => 'ghana_standard',
    'gradingScaleJson' => '[]',
    'schoolName' => 'Test School',
    'schoolSubtitle' => 'Test Subtitle',
    'siteLogoUrl' => '',
    'sidebarLabels' => [
        'programs' => 'Programs',
        'courses' => 'Courses',
        'students' => 'Students',
        'lecturers' => 'Teachers',
        'attendance' => 'Attendance',
        'exams' => 'Exams & Results',
        'fees' => 'Fees & Payments',
        'reports' => 'Reports',
        'portal' => 'Portal',
        'settings' => 'Settings',
        'guide' => 'Guide',
    ],
]);

echo substr($view->render(), 0, 1000);
