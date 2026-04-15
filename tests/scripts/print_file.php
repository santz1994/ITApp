<?php
$path = $argv[1] ?? null;
if (!$path) { echo "Usage: php print_file.php <path>\n"; exit(1); }
$lines = file($path);
foreach ($lines as $i => $line) {
    printf("%4d: %s", $i+1, rtrim($line, "\r\n") . PHP_EOL);
}
