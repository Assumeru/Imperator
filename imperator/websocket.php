<?php
require_once './app/Imperator.php';

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