import requests
import urllib
import json
import sqlite3
import datetime

def getheroes():
	heros = {
		"Barbarian King": 0,
		"Archer Queen": 0,
		"Grand Warden": 0
	}
	return heros

def getspells():
	spells = {
		"Lightning Spell": 0,
		"Healing Spell": 0,
		"Rage Spell": 0,
		"Jump Spell": 0,
		"Freeze Spell": 0,
		"Poison Spell": 0,
		"Earthquake Spell": 0,
		"Haste Spell": 0,
		"Clone Spell": 0,
		"Skeleton Spell": 0,
		"Bat Spell": 0
	}
	return spells

def gettroops():
	troops = {
		"Barbarian": 0,
		"Archer": 0,
		"Goblin": 0,
		"Giant": 0,
		"Wall Breaker": 0,
		"Balloon": 0,
		"Wizard": 0,
		"Healer": 0,
		"Dragon": 0,
		"P.E.K.K.A": 0,
		"Minion": 0,
		"Hog Rider": 0,
		"Valkyrie": 0,
		"Golem": 0,
		"Witch": 0,
		"Lava Hound": 0,
		"Bowler": 0,
		"Baby Dragon": 0,
		"Miner": 0,
		"Wall Wrecker": 0,
		"Battle Blimp": 0,
		"Ice Golem": 0,
		"Electro Dragon": 0,
		"Stone Slammer": 0
	}
	return troops

clantag = "#20L8UCRRC"	# NAMU
url = "https://api.clashofclans.com/v1/clans/" + urllib.quote_plus(clantag)
token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6IjA0YjU0NDlhLWM4ZDEtNDQ3Zi1iZWMwLTU1ZjhiMTA5ZjA3ZSIsImlhdCI6MTU1MTA3NjU5Mywic3ViIjoiZGV2ZWxvcGVyL2ZiMzE0MTE0LTljY2ItODhmZC0yMTljLTc4MWIxMGU4NDMxNiIsInNjb3BlcyI6WyJjbGFzaCJdLCJsaW1pdHMiOlt7InRpZXIiOiJkZXZlbG9wZXIvc2lsdmVyIiwidHlwZSI6InRocm90dGxpbmcifSx7ImNpZHJzIjpbIjExMi4yMTcuMTI2LjYwIl0sInR5cGUiOiJjbGllbnQifV19.OfFyFHrRO2J8H5C_DOsiNZXq63l8WbXQLLNb7RPgFUFV49yFd7cy317bu9uesvV3FLZZIEErur_nka7EhRNljA"
headers = {
    "Accept": "application/json",
    "Authorization": "Bearer " + token
}
response = requests.get(url, headers = headers).json()
memberList = response["memberList"]

db = sqlite3.connect("/var/www/xor/coc/db/namu.db")

for member in memberList:
	url = "https://api.clashofclans.com/v1/players/" + urllib.quote_plus(member["tag"])
	r = requests.get(url, headers = headers).json()

	if "league" in member:
		league = member["league"]["name"]
	else:
		league = "Unranked"

	if "clanRank" in member:
		rank = member["clanRank"]
	else:
		rank = 0

	if "townHallWeaponLevel" in r:
		weapon = r["townHallWeaponLevel"]
	else:
		weapon = 0

	cursor = db.cursor()
	cursor.execute("INSERT INTO journal (tag, name, league, role, clanRank, town_hall, town_hall_weapon, exp_level, war_stars, attack_wins, versus_battle_wins, troops_donated, troops_received) VALUES ('{}', '{}', '{}', '{}', {}, {}, {}, {}, {}, {}, {}, {}, {})".format(r["tag"], r["name"].encode('utf8').replace("'", "''"), league, r["role"], rank, r["townHallLevel"], weapon, r["expLevel"], r["warStars"], r["attackWins"], r["versusBattleWins"], r["donations"], r["donationsReceived"]))
	journal = cursor.lastrowid

	heroes = getheroes()
	for h in r["heroes"]:
		if h["village"] != "home":
			continue
		name = h["name"]
		if name in heroes:
			heroes[name] = h["level"]
	keys = "journal"
	values = str(journal)
	for name in heroes:
		if heroes[name] == 0:
			continue
		keys += ", [" + name + "]"
		values +=  ", " + str(heroes[name])
	cursor.execute("INSERT INTO heroes ({}) VALUES ({})".format(keys, values))

	spells = getspells()
	for s in r["spells"]:
		if s["village"] != "home":
			continue
		name = s["name"]
		if name in spells:
			spells[name] = s["level"]
	keys = "journal"
	values = str(journal)
	for name in spells:
		if spells[name] == 0:
			continue
		keys += ", [" + name + "]"
		values +=  ", " + str(spells[name])
	cursor.execute("INSERT INTO spells ({}) VALUES ({})".format(keys, values))

	troops = gettroops()
	for t in r["troops"]:
		if t["village"] != "home":
			continue
		name = t["name"]
		if name in troops:
			troops[name] = t["level"]
	keys = "journal"
	values = str(journal)
	for name in troops:
		if troops[name] == 0:
			continue
		keys += ", [" + name + "]"
		values +=  ", " + str(troops[name])
	cursor.execute("INSERT INTO troops ({}) VALUES ({})".format(keys, values))

	keys = "journal"
	values = str(journal)
	for i in range(0, 34):
		keys += ", achievement" + str(i)
		values += ", " + str(r["achievements"][i]["value"])
	cursor.execute("INSERT INTO achievements ({}) VALUES ({})".format(keys, values))

	print(datetime.datetime.now().strftime("%Y-%m-%d %X ") + unicode(member["name"]).encode('utf8'))

db.commit()
db.close()
