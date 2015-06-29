<?php
namespace imperator\api\requests;
use imperator\Imperator;

class AutoRollGameRequest extends GameRequest {
	private $autoroll;

	public function __construct($gid, $autoroll) {
		parent::__construct($gid);
		if($autoroll == 'false') {
			$this->autoroll = false;
		} else {
			$this->autoroll = (bool)$autoroll;
		}
	}

	public function getType() {
		return 'autoroll';
	}

	protected function getAutoRoll() {
		return $this->autoroll;
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$player = $this->getGame()->getPlayerByUser($user);
		$player->setAutoRoll($this->getAutoRoll());
		Imperator::getDatabaseManager()->getTable('GamesJoined')->saveAutoRoll($player);
		return array(
			'autoroll' => $player->getAutoRoll()
		);
	}
}