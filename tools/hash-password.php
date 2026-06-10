<?php

declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Pakai: php tools/hash-password.php password\n");
    exit(1);
}

echo password_hash($argv[1], PASSWORD_BCRYPT) . PHP_EOL;
