CREATE TABLE imperator_users (
	uid				INT					NOT NULL	AUTO_INCREMENT,
	wins			INT					NOT NULL	DEFAULT "0",
	losses			INT					NOT NULL	DEFAULT "0",
	score			INT					NOT NULL	DEFAULT "0",
	PRIMARY KEY(uid)
) ;
CREATE TABLE imperator_games (
	gid				INT					NOT NULL	AUTO_INCREMENT,
	map				SMALLINT			NOT NULL,
	name			VARCHAR(255)		NOT NULL,
	uid				INT					NOT NULL,
	turn			INT					NOT NULL	DEFAULT "0",
	time			INT					NOT NULL,
	state			SMALLINT			NOT NULL	DEFAULT "0",
	units			INT					NOT NULL	DEFAULT "0",
	conquered		SMALLINT			NOT NULL	DEFAULT "0",
	password		CHAR(40),
	PRIMARY KEY(gid)
) ;
CREATE TABLE imperator_gamesjoined (
	uid				INT					NOT NULL,
	gid				INT					NOT NULL,
	color			CHAR(6)				NOT NULL,
	autoroll		SMALLINT			NOT NULL	DEFAULT "1",
	mission			INT					NOT	NULL	DEFAULT "0",
	m_uid			INT					NOT NULL	DEFAULT "0",
	state			INT					NOT NULL	DEFAULT "0",
	c_art			SMALLINT			NOT NULL	DEFAULT "0",
	c_cav			SMALLINT			NOT NULL	DEFAULT "0",
	c_inf			SMALLINT			NOT NULL	DEFAULT "0",
	c_jok			SMALLINT			NOT NULL	DEFAULT "0",
	PRIMARY KEY(uid,gid)
) ;
CREATE TABLE imperator_territories (
	gid				INT					NOT NULL,
	territory			VARCHAR(150)		NOT NULL,
	uid				INT					NOT NULL,
	units			SMALLINT			NOT NULL,
	PRIMARY KEY(gid,territory)
) ;
CREATE TABLE imperator_attacks (
	gid				INT					NOT NULL,
	a_territory		VARCHAR(150)		NOT NULL,
	d_territory		VARCHAR(150)		NOT NULL,
	a_uid			INT					NOT NULL,
	d_uid			INT					NOT NULL,
	a_roll			CHAR(3)				NOT NULL,
	transfer		INT					NOT NULL,
	PRIMARY KEY(gid,a_territory,d_territory)
) ;
CREATE TABLE imperator_chat (
	gid				INT					NOT NULL,
	uid				INT					NOT NULL,
	time			INT					NOT NULL,
	message			VARCHAR(512),
	PRIMARY KEY(gid,uid,time)
) ;
CREATE TABLE imperator_combatlog (
	gid				INT					NOT NULL,
	time			INT					NOT NULL,
	message			VARCHAR(512)
) ;