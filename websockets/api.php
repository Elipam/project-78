<?php
use React\EventLoop\Factory;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\socket;
require 'myApp.php';
require 'vendor/autoload.php';

$app = new \Ratchet\Http\HttpServer(
    new \Ratchet\WebSocket\WsServer(
        new socket()
    )
);

$loop = Factory::create();
$socketServer = new React\Socket\Server('127.0.0.1:8881', $loop);



$secure_websockets_server = new \Ratchet\Server\IoServer($app, $socketServer, $loop);
$secure_websockets_server->run();
