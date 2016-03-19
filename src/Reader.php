<?php

namespace Duffleman\Temperature;

use Duffleman\Temperature\Exceptions\SocketException;

/**
 * Class Reader
 *
 * @package Duffleman\Temperature
 */
class Reader
{

    /**
     * Holds the socket we are using.
     *
     * @var
     */
    private $socket;

    /**
     * Holds the IP address we are connecting to. (The Omega Box)
     *
     * @var string
     */
    private $address;

    /**
     * The port we are connecting on.
     *
     * @var int
     */
    private $port;

    /**
     * Channels to get before disconnecting.
     *
     * @var array
     */
    private $channels = ['A', 'B', 'D'];

    /**
     * The result set of each channel.
     *
     * @var array
     */
    private $result = [];

    /**
     * The regex string to match the output result to.
     * Here so I can easily change it in the future.
     *
     * @var string
     */
    private $preg_match = "/T(.)(\\d+.\\d+)(F|C)/";

    /**
     * Should the socket be maintained?
     *
     * @var bool
     */
    private $maintain = false;

    /**
     * Reader constructor.
     *
     * @param string $address
     * @param int    $port
     * @param bool   $run_on_construct
     */
    public function __construct($address, $port = 2000, $run_on_construct = true)
    {
        $this->address = $address;
        $this->port = $port;
        $this->buildSocket();

        if ($run_on_construct) {
            $this->run();
        }
    }

    /**
     * Build the socket up.
     *
     * @throws SocketException
     */
    private function buildSocket()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->checkSocket();
    }

    /**
     * Check if the socket was built successfully.
     *
     * @throws SocketException
     */
    private function checkSocket()
    {
        if ($this->socket === false) {
            throw new SocketException(socket_strerror(socket_last_error()));
        }
    }

    /**
     * Read from the socket, grab the result, then close it all down.
     *
     * @return \Generator|array
     * @throws ReaderException
     */
    public function run()
    {
        $this->connect();

        if (!$this->maintain) {
            $matched = $this->channels;
            while (($result = socket_read($this->socket, 2048, PHP_BINARY_READ)) && !empty($matched)) {
                list($original, $channel, $temperature, $format) = $this->format($result);

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
        } else {
            while (($result = socket_read($this->socket, 2048, PHP_BINARY_READ))) {
                list($original, $channel, $temperature, $format) = $this->format($result);
                $result = [
                    'original'    => $original,
                    'channel'     => $channel,
                    'temperature' => $temperature,
                    'format'      => $format,
                ];
                yield($result);
            }
        }

        $this->disconnect();
    }

    /**
     * Connect to the address.
     * Also checks to ensure it worked.
     *
     * @throws SocketException
     */
    private function connect()
    {
        $this->connection = socket_connect($this->socket, $this->address, $this->port);
        $this->checkConnection();
    }

    /**
     * Check if the connection was established.
     *
     * @throws SocketException
     */
    private function checkConnection()
    {
        if ($this->connection === false) {
            throw new SocketException(socket_strerror(socket_last_error($this->socket)));
        }
    }

    /**
     * @param $input_line
     * @return array
     * @throws ReaderException
     */
    public function format($input_line)
    {
        $output_array = [];
        preg_match($this->preg_match, $input_line, $output_array);
        if (empty($output_array)) {
            throw new ReaderException("Unable to format result, given: {$input_line}.");
        }

        return $output_array;
    }

    /**
     * Disconnect from the socket.
     */
    private function disconnect()
    {
        socket_close($this->socket);
    }

    /**
     * @param $channel
     * @return mixed
     */
    public function get($channel)
    {
        return $this->result[$channel];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->result;
    }

    /**
     * Should the socket be maintained?
     *
     * @return $this
     */
    public function maintain()
    {
        $this->maintain = !$this->maintain;

        return $this;
    }
}