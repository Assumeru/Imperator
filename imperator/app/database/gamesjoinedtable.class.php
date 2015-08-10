<?php
namespace imperator\database;
use imperator\Imperator;

class GamesJoinedTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('GAMESJOINED', 'imperator_gamesjoined', array(
			'GAME' => 'gid',
			'USER' => 'uid',
			'COLOR' => 'color',
			'AUTOROLL' => 'autoroll',
			'MISSION' => 'mission',
			'MISSION_USER' => 'm_uid',
			'STATE' => 'state',
			'CARD_ARTILLERY' => 'c_art',
			'CARD_CAVALRY' => 'c_cav',
			'CARD_INFANTRY' => 'c_inf',
			'CARD_JOKER' => 'c_jok'
		));
	}

	public function create() {
		$this->getManager()->preparedStatement(
			'CREATE TABLE @GAMESJOINED (
				@-GAMESJOINED.GAME INT REFERENCES @GAMES(@-GAMES.GAME),
				@-GAMESJOINED.USER INT REFERENCES @OUTSIDEUSERS(@-OUTSIDEUSERS.USER),
				@-GAMESJOINED.COLOR CHAR(6) NOT NULL,
				@-GAMESJOINED.AUTOROLL SMALLINT NOT NULL DEFAULT "1",
				@-GAMESJOINED.MISSION INT NOT NULL DEFAULT "0",
				@-GAMESJOINED.MISSION_USER INT NOT NULL DEFAULT "0",
				@-GAMESJOINED.STATE INT NOT NULL DEFAULT "0",
				@-GAMESJOINED.CARD_ARTILLERY SMALLINT NOT NULL DEFAULT "0",
				@-GAMESJOINED.CARD_CAVALRY SMALLINT NOT NULL DEFAULT "0",
				@-GAMESJOINED.CARD_INFANTRY SMALLINT NOT NULL DEFAULT "0",
				@-GAMESJOINED.CARD_JOKER SMALLINT NOT NULL DEFAULT "0",
				PRIMARY KEY(@-GAMESJOINED.GAME, @-GAMESJOINED.USER)
			)'
		);
	}

	public function drop() {
		$this->getManager()->preparedStatement('DROP TABLE IF EXISTS @GAMESJOINED');
	}

	public function removeUsersFromGame(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @GAMESJOINED WHERE @GAMESJOINED.GAME = %d',
			$game->getId()
		)->free();
	}

	public function addUserToGame(\imperator\game\Player $user) {
		$this->getManager()->insert('@GAMESJOINED', array(
			'@GAMESJOINED.GAME' => $user->getGame()->getId(),
			'@GAMESJOINED.USER' => $user->getId(),
			'@GAMESJOINED.COLOR' => $user->getColor()
		))->free();
	}

	public function removeUserFromGame(\imperator\game\Player $user) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @GAMESJOINED WHERE @GAMESJOINED.GAME = %d AND @GAMESJOINED.USER = %d',
			$user->getGame()->getId(), $user->getId()
		)->free();
	}

	/**
	 * 
	 * @param \imperator\Game $game
	 * @return \imperator\game\Player[]
	 */
	public function getPlayersForGame(\imperator\Game $game) {
		$players = array();
		$query = $this->getManager()->preparedStatement(
			'SELECT @OUTSIDEUSERS.USERNAME, @OUTSIDEUSERS.USER, @GAMESJOINED.COLOR, @GAMESJOINED.STATE,
			@GAMESJOINED.MISSION, @GAMESJOINED.MISSION_USER, @GAMESJOINED.AUTOROLL,
			@GAMESJOINED.CARD_ARTILLERY, @GAMESJOINED.CARD_CAVALRY, @GAMESJOINED.CARD_INFANTRY, @GAMESJOINED.CARD_JOKER
			FROM @OUTSIDEUSERS
			JOIN @GAMESJOINED
			ON (@OUTSIDEUSERS.USER = @GAMESJOINED.USER)
			WHERE @GAMESJOINED.GAME = %d
			ORDER BY @OUTSIDEUSERS.USERNAME',
			$game->getId()
		);
		$userClass = Imperator::getSettings()->getUserClass();
		$missions = $game->getMap()->getMissions();
		while($result = $query->fetchResult()) {
			$player = new \imperator\game\Player(new $userClass(
				$result->getInt(1),
				$result->get(0)
			), $game);
			$player->setColor($result->get(2));
			$player->setState($result->getInt(3));
			$player->setAutoRoll($result->getBool(6));
			$player->setCards(new \imperator\game\Cards(
				$result->getInt(7),
				$result->getInt(8),
				$result->getInt(9),
				$result->getInt(10)
			));
			$mission = new \imperator\mission\PlayerMission($missions[$result->getInt(4)], $player);
			$mission->setUid($result->getInt(5));
			$player->setMission($mission);
			$players[] = $player;
		}
		$query->free();
		return $players;
	}

	public function saveMissions(array $players) {
		foreach($players as $player) {
			$mission = $player->getMission();
			$this->getManager()->preparedStatement(
				'UPDATE @GAMESJOINED SET @GAMESJOINED.MISSION = %d, @GAMESJOINED.MISSION_USER = %d
				WHERE @GAMESJOINED.USER = %d AND @GAMESJOINED.GAME = %d',
				$mission->getId(), $mission->getUid(), $player->getId(), $player->getGame()->getId()
			)->free();
		}
	}

	/**
	 * @param int $gid
	 * @param \imperator\Member $user
	 * @return bool
	 */
	public function gameContainsPlayer($gid, \imperator\Member $user) {
		$db = $this->getManager();
		return $db->exists($db->preparedStatement(
			'SELECT 1 FROM @GAMESJOINED WHERE @GAMESJOINED.GAME = %d AND @GAMESJOINED.USER = %d',
			$gid, $user->getId()
		));
	}

	public function forfeit(\imperator\game\Player $user) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMESJOINED SET @GAMESJOINED.STATE = %d, @GAMESJOINED.AUTOROLL = %b
			WHERE @GAMESJOINED.USER = %d AND @GAMESJOINED.GAME = %d',
			\imperator\game\Player::STATE_GAME_OVER, true, $user->getId(), $user->getGame()->getId()
		)->free();
	}

	/**
	 * @param \imperator\game\Player $user
	 * @return \imperator\game\Cards
	 */
	public function getCardsFor(\imperator\game\Player $user) {
		$query = $this->getManager()->preparedStatement(
			'SELECT @GAMESJOINED.CARD_ARTILLERY, @GAMESJOINED.CARD_CAVALRY, @GAMESJOINED.CARD_INFANTRY, @GAMESJOINED.CARD_JOKER
			FROM @GAMESJOINED
			WHERE @GAMESJOINED.GAME = %d AND @GAMESJOINED.USER = %d',
			$user->getGame()->getId(), $user->getId()
		);
		$result = $query->fetchResult();
		$query->free();
		return new \imperator\game\Cards(
			$result->getInt(0),
			$result->getInt(1),
			$result->getInt(2),
			$result->getInt(3)
		);
	}

	public function getNumberOfJokers(\imperator\Game $game) {
		$query = $this->getManager()->preparedStatement(
			'SELECT SUM(@GAMESJOINED.CARD_JOKER) FROM @GAMESJOINED WHERE @GAMESJOINED.GAME = %d',
			$game->getId()
		);
		$result = $query->fetchResult();
		$query->free();
		return $result->getInt(0);
	}

	public function saveCards(\imperator\game\Player $user) {
		$cards = $user->getCards();
		$this->getManager()->preparedStatement(
			'UPDATE @GAMESJOINED SET
			@GAMESJOINED.CARD_ARTILLERY = %d, @GAMESJOINED.CARD_CAVALRY = %d,
			@GAMESJOINED.CARD_INFANTRY = %d, @GAMESJOINED.CARD_JOKER = %d
			WHERE @GAMESJOINED.GAME = %d AND @GAMESJOINED.USER = %d',
			$cards->getArtillery(), $cards->getCavalry(), $cards->getInfantry(), $cards->getJokers(),
			$user->getGame()->getId(), $user->getId()
		)->free();
	}

	public function saveState(\imperator\game\Player $user) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMESJOINED SET @GAMESJOINED.STATE = %d WHERE @GAMESJOINED.GAME = %d AND @GAMESJOINED.USER = %d',
			$user->getState(), $user->getGame()->getId(), $user->getId()
		)->free();
	}

	public function saveAutoRoll(\imperator\game\Player $user) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMESJOINED SET @GAMESJOINED.AUTOROLL = %b WHERE @GAMESJOINED.GAME = %d AND @GAMESJOINED.USER = %d',
			$user->getAutoRoll(), $user->getGame()->getId(), $user->getId()
		)->free();
	}
}