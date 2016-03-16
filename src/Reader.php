<?php

namespace Duffleman\Temperature;

use Duffleman\Temperature\Exceptions\SocketException;

class Reader
{

    private $socket;
    private $address;
    private $port;
    private $channels = ['A', 'B', 'D'];
    private $result = [];

    public function __construct($address, $port = 2000)
    {
        $this->address = $address;
        $this->port = $port;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->checkSocket();
        $this->connection = socket_connect($this->socket, $address, $port);
        $this->checkConnection();
    }

    private function checkSocket()
    {
        if ($this->socket === false) {
            throw new SocketException(socket_strerror(socket_last_error()));
        }
    }

    private function checkConnection()
    {
        if ($this->connection === false) {
            throw new SocketException(socket_strerror(socket_last_error($this->socket)));
        }
    }

    public function run()
    {
        $matched = $this->channels;
        while (($result = socket_read($this->socket, 2048, PHP_BINARY_READ)) && !empty($matched)) {
            list($original, $channel, $temperature, $format) = read($result);

            $this->result[$channel] = [
                'original'    => $original,
                'channel'     => $channel,
                'temperature' => $temperature,
                'format'      => $format,
            ];

            if (in_array($channel, $matched)) {
                $key = array_search($channel, $matched, true);
                unset($matched[$key]);
            }
        }
    }

    public function get($channel)
    {
        return $this->result[$channel];
    }

}