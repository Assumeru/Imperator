<?php
require_once '../imperator/app/Imperator.php';

$maps = \imperator\map\Map::getMaps();

foreach($maps as $map) {
	generatePo($map);
}

function generatePo(\imperator\map\Map $map) {
	$po =
'# Map '.$map->getName().'
#, fuzzy
msgid ""
msgstr ""
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"

#: etc/maps/'.$map->getId().'.xml

# Name
msgid "'.$map->getName().'"
msgstr ""

# Territories';
	foreach($map->getTerritories() as $territory) {
		$po .= '
msgid "'.$territory->getName().'"
msgstr ""
';
	}
	$po .= '
# Regions';
	foreach($map->getRegions() as $region) {
		$po .= '
msgid "'.$region->getName().'"
msgstr ""
';
	}
	$po .= '
# Missions';
	foreach($map->getMissions() as $mission) {
		if($mission instanceof \imperator\mission\DominationMission || $mission instanceof \imperator\mission\RivalryMission) {
			continue;
		}
		$po .= '
msgid "'.$mission->getName().'"
msgstr ""
msgid "'.$mission->getDescription(\imperator\Language::getInstance()).'"
msgstr ""
';
	}
	file_put_contents('./map_'.$map->getId().'.po', $po);
}