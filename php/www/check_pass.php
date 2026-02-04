<?php
$candidates = [
    'Dameng123',
    'SYSDBA',
    '123456',
    '123456789',
    '123abc!@#'
];

echo "Testing passwords...\n";

foreach ($candidates as $pwd) {
    echo "Trying: $pwd ... ";
    $link = @dm_connect("dm8:5236", "SYSDBA", $pwd);
    if ($link) {
        echo "[SUCCESS] Correct password is: $pwd\n";
        dm_close($link);
        exit(0);
    } else {
        echo "[FAILED] " . dm_error() . "\n";
    }
}
echo "All attempts failed.\n";
