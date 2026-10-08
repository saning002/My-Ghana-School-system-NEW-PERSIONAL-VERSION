<?php
$before = get_declared_classes();
require __DIR__.'/../app/Providers/AppServiceProvider.php';
$after = get_declared_classes();
$diff = array_values(array_diff($after, $before));
foreach ($diff as $c) {
    if (str_contains($c, 'AppServiceProvider')) {
        echo $c.PHP_EOL;
    }
}
echo "\nTotal new classes: ".count($diff)."\n";
echo 'class_exists: '.(class_exists('App\\Providers\\AppServiceProvider') ? 'true' : 'false')."\n";