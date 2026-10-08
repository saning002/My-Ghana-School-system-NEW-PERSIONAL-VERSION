<?php
$path = __DIR__.'/../app/Providers/AppServiceProvider.php';
$contents = file_get_contents($path);
echo 'MD5: '.md5($contents)."\n";
echo 'First 300 bytes hex:\n'.substr(bin2hex($contents),0,600)."\n";
echo "\nContents with visible chars:\n";
for ($i=0;$i<min(300, strlen($contents)); $i++) {
    $c = $contents[$i];
    $ord = ord($c);
    if ($ord >= 32 && $ord <=126) echo $c; else echo '['.$ord.']';
}
echo "\n";
