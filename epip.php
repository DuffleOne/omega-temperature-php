<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

$address = isset($argv[1]) ? $argv[1] : '192.168.254.111';
$port = isset($argv[2]) ? $argv[2] : '2000';

$temp = [];
$temp[] = read(get_raw_temperature($address, $port)); # Channel A
$temp[] = read(get_raw_temperature($address, $port)); # Channel B
$temp[] = read(get_raw_temperature($address, $port)); # Difference

foreach ($temp as $t) {
    if (!is_array($t)) {
        echo("A channel failed to respond appropriately, instead sent: {$t}.");
    } else {
        // WHere $t[1] is the channel, A, B or D.
        // Where $t[2] is the result.
        // Where $t[3] is the format, C or F.
        echo("Channel {$t[1]} reports: {$t[2]} {$t[3]}.");
    }
    echo("\n");
}

function get_raw_temperature($address, $port)
{
    /* Create a TCP/IP socket. */
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        die("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
    }

    $result = socket_connect($socket, $address, $port);
    if ($result === false) {
        die("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n");
    }

    while ($result = socket_read($socket, 2048, PHP_BINARY_READ)) {
        return $result;
    }
}

function read($input_line)
{
    $output_array = [];
    preg_match("/(T.)(\\d+.\\d+)(F|C)/", $input_line, $output_array);

    if (!empty($output_array)) {
        return $output_array;
    }

    return $input_line;
}