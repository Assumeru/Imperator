<?php
namespace imperator\page;
use imperator\Imperator;

class YourGameList extends GameList {
	const NAME = 'My Games';
	const URL = 'my-games';

	protected function getGames(\imperator\User $user) {
		return Imperator::getDatabaseManager()->getTable('Games')->getGamesFor($user);
	}
}