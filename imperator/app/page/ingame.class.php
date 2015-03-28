<?php
namespace imperator\page;

class InGame extends Page {
	/**
	 * @var \imperator\Game
	 */
	private $game = null;

	public function __construct(\imperator\Game $game) {
		$this->game = $game;
	}

	public function render(\imperator\User $user) {
		echo $this->game->getName().' has started.';
	}
}