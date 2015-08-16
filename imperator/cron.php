<?php
require_once './app/imperator.class.php';

$settings = \imperator\Imperator::getSettings();
$db = \imperator\Imperator::getDatabaseManager();
\imperator\Imperator::getLogger()->log(\imperator\Logger::LEVEL_DEBUG, 'Starting cron');
$db->getChatTable()->deleteOldMessages(time() - $settings->getMaxChatMessageAge(), $settings->getMinNumChatMessagesToPreserve());
$db->getGamesTable()->deleteOldGames(time() - $settings->getMaxFinishedGameAge(), time() - $settings->getInactiveGameTime());