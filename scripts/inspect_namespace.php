<?php
$path = __DIR__.'/../app/Providers/AppServiceProvider.php';
$contents = file_get_contents($path);
$lines = explode("\n", $contents);
foreach ($lines as $i => $line) {
    if (str_contains($line, 'namespace')) {
        echo "Line ".($i+1).": ". $line ."\n";
        $s = $line;
        echo "Length: ".strlen($s)."\n";
        for ($j=0;$j<strlen($s);$j++) {
            echo $j.': '.ord($s[$j]).' '.($s[$j] === '\t' ? '\\t' : $s[$j])."\n";
        }
        break;
    }
}
