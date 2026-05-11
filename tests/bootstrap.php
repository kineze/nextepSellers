<?php

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
$cachedConfig = dirname(__DIR__) . '/bootstrap/cache/config.php';

if (is_file($cachedConfig)) {
    fwrite(STDERR, PHP_EOL . 'Refusing to run tests while Laravel config is cached.' . PHP_EOL);
    fwrite(STDERR, 'Run `php artisan config:clear` first so phpunit.xml can force the SQLite test database.' . PHP_EOL . PHP_EOL);
    exit(1);
}

require $autoload;
