<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$route = current_route();

require dirname(__DIR__) . '/routes/web.php';
