<?php
$candidates = [
    'SYSDBA',
    '123456'
];

echo "Diagnosing DM8 Password...\n";

foreach ($candidates as $pwd) {
    echo "Attempting with password: '$pwd' ... ";
    $link = @dm_connect("dm8:5236", "SYSDBA", $pwd);
    if ($link) {
        echo "[SUCCESS] Connected!\n";
        dm_close($link);
    } else {
        echo "[FAILED] " . dm_error() . "\n";
    }
}
