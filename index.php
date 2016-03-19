<?php

use Duffleman\Temperature\Reader;

require_once('vendor/autoload.php');

if (!isset($argv[1])) {
    die("You need to pass in the IP address of the Omega box as a script argument.");
}

$port = isset($argv[2]) ? $argv[2] : 2000;

// Normal
$reader = new Reader($argv[1], $port);
$results = $reader->getAll();

var_dump($results);

// Generator
$reader = (new Reader($argv[1], $port, false))->maintain();
foreach($reader->run() as $result)
{
    var_dump($result);
}