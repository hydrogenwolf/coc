<?php

require_once('token.php');

$roles = array(
		"leader" => "Leader",
		"coLeader" => "Co-leader",
		"admin" => "Elder",
		"member" => "Member",
		);

$hero_names = array(
		"Barbarian King", 
		"Archer Queen", 
		"Grand Warden"
		);

$troop_names = array(
		"Barbarian",
		"Archer",
		"Goblin",
		"Giant",
		"Wall Breaker",
		"Balloon",
		"Wizard",
		"Healer",
		"Dragon",
		"P.E.K.K.A",
		"Minion",
		"Hog Rider",
		"Valkyrie",
		"Golem",
		"Witch",
		"Lava Hound",
		"Bowler",
		"Baby Dragon",
		"Miner",
		"Wall Wrecker",
		"Battle Blimp",
		"Ice Golem",
		"Electro Dragon",
		"Stone Slammer"
		);

$spell_names = array(
		"Lightning Spell",
		"Healing Spell",
		"Rage Spell",
		"Jump Spell",
		"Freeze Spell",
		"Poison Spell",
		"Earthquake Spell",
		"Haste Spell",
		"Clone Spell",
		"Skeleton Spell",
		"Bat Spell"
		);

$achievement_names = array(
		"achievement0" => "Bigger Coffers", 
		"achievement1" => "Get those Goblins!", 
		"achievement2" => "Bigger & Better",
		"achievement3" => "Nice and Tidy",
		"achievement4" => "Release the Beasts",
		"achievement5" => "Gold Grab",
		"achievement6" => "Elixir Escapade",
		"achievement7" => "Sweet Victory!",
		"achievement8" => "Empire Builder",
		"achievement9" => "Wall Buster",
		"achievement10" => "Humiliator",
		"achievement11" => "Union Buster",
		"achievement12" => "Conqueror",
		"achievement13" => "Unbreakable",
		"achievement14" => "Friend in Need",
		"achievement15" => "Mortar Mauler",
		"achievement16" => "Heroic Heist",
		"achievement17" => "League All-Star",
		"achievement18" => "X-Bow Exterminator",
		"achievement19" => "Firefighter",
		"achievement20" => "War Hero",
		"achievement21" => "Treasurer",
		"achievement22" => "Anti-Artillery",
		"achievement23" => "Sharing is caring",
		"achievement24" => "Keep your village safe",
		"achievement25" => "Master Engineering",
		"achievement26" => "Next Generation Model",
		"achievement27" => "Un-Build It",
		"achievement28" => "Champion Builder",
		"achievement29" => "High Gear",
		"achievement30" => "Hidden Treasures",
		"achievement31" => "Games Champion",
		"achievement32" => "Dragon Slayer",
		"achievement33" => "War League Legend",
		"achievement34" => "Keep your village safe"
		);

function curl($url)
{
	global $token;

	$ch = curl_init($url);
	$header = array();
	$header[] = "Accept: application/json";
	$header[] = "Authorization: Bearer " . $token;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$result = curl_exec($ch);
	curl_close($ch);

	return json_decode($result, true);
}

$offensives = array($hero_names, $troop_names, $spell_names);

function total_levels($data)
{
	global $offensives;
	$total = 0;

	foreach($offensives as $offensive)
	{
		foreach($offensive as $o)
		{
			$total += $data[$o];
		}
	}

	return $total;
}

function total_levels_raw($data)
{
	$total = 0;

	foreach ($data["heroes"] as $hero)
	{
		if ($hero["village"] != "home")	continue;
		$total += $hero["level"];
	}

	foreach ($data["spells"] as $spell)
	{
		if ($spell["village"] != "home")	continue;
		$total += $spell["level"];
	}

	foreach ($data["troops"] as $troop)
	{
		if ($troop["village"] != "home")	continue;
		$total += $troop["level"];
	}

	return $total;
}

$names_for_query = "";

foreach($offensives as $offensive)
{
	foreach($offensive as $name)
	{
		if(strlen($names_for_query) > 0)	$names_for_query .= ", ";
		$names_for_query .= "[$name]";
	}
}

?>
