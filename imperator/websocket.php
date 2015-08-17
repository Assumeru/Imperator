<?php
require_once './app/imperator.class.php';

$settings = \imperator\Imperator::getSettings();
$handler = $settings->getWebSocketHandler();
$ws = new \imperator\websocket\WebSocket(
	$settings->getWebSocketAddress(),
	$settings->getWebSocketPort(),
	$settings->getWebSocketPath(),
	\imperator\Imperator::getLogger(),
	new $handler()
);
$ws->start();