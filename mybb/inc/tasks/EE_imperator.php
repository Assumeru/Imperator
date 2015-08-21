<?php
	if(!defined('IN_MYBB')) {
		die;
	}

	function task_EE_imperator($task) {
		//Edit this if you're using a different install path
		require_once MYBB_ROOT.'/imperator/app/imperator.class.php';

		$cron = new \imperator\Cron();
		$numChats = $cron->cleanChat();
		$numGames = $cron->cleanGames();

		add_task_log($task, 'EE_imperator cleanup task completed: '.$numChats.' chat messages and '.$games.' games deleted.');
	}