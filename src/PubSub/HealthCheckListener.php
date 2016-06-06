<?php

namespace Google\Cloud\Samples\Bookshelf\PubSub;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Psr\Log\LoggerInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\ConnectionInterface;

class HealthCheckListener implements HttpServerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        // send the 200 OK health response and return
        $response = new Response(200, ['Content-Length' => 0]);
        $conn->send((string) $response);
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
    }

    public function onClose(ConnectionInterface $conn)
    {
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->log(sprintf('An error has occurred: %s', $e->getMessage()));
        $conn->close();
    }

    private function log($message)
    {
        if ($this->logger) {
            $this->logger->log($message);
        }
    }
}
