<?php

require 'vendor/autoload.php';

use App\Bootstrap;

if ($argc !== 2) {
    echo "Usage: php script.php input.csv\n";
    exit(1);
}

$bootstrap = new Bootstrap();
$results = $bootstrap->run($argv[1]);

foreach ($results as $fee) {
    echo $fee . "\n";
}
