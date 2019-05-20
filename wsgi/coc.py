import requests

roles =		{
		"leader": "Leader",
		"coLeader": "Co-leader",
		"admin": "Elder",
		"member": "Member"
		}

hero_names =	[ 
		"Barbarian King", 
		"Archer Queen", 
		"Grand Warden" 
		]

troop_names =	[
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
		]

spell_names =	[
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
		]

offensives = hero_names + troop_names + spell_names

names_for_query = ""
for offensive in offensives:
	if len(names_for_query) > 0:
		names_for_query += ", "
	names_for_query += "[%s]" % offensive


def total_levels(data):
	total = 0
	for offensive in offensives:
		if data[offensive]:
			total += data[offensive]
	return total

def total_levels_raw(data):
	total = 0
	for hero in data["heroes"]:
		if hero["village"] != "home":
			continue
		total += hero["level"]

	for spell in data["spells"]:
		if spell["village"] != "home":
			continue
		total += spell["level"]

	for troop in data["troops"]:
		if troop["village"] != "home":
			continue
		total += troop["level"]

	return total

def curl(url, token):
	headers = {
	    "Accept": "application/json",
	    "Authorization": "Bearer " + token
	}
	response = requests.get(url, headers = headers).json()

	return response

