import sqlite3
import urllib
import urlparse
import json
import datetime

import sys, os
sys.path.append(os.path.dirname(__file__))
from settings import *
import coc

def application(environ, start_response):
	params = urlparse.parse_qs(environ['QUERY_STRING'])
	mode = params.get('m', [None])[0]
	tag = params.get('t', [None])[0]
	tag = '#' + tag
	response = json.dumps(sheet(tag, mode))

	response_headers = [('Content-type', 'application/json'), ('Content-Length', str(len(response)))]
	start_response('200 OK', response_headers)
	return [response]

def sheet(tag, mode):
	url = "https://api.clashofclans.com/v1/players/" + urllib.quote_plus(tag)
	result = coc.curl(url, Settings.token)

	town_hall = str(result["townHallLevel"])
	if "townHallWeaponLevel" in result:
		town_hall += "-" + str(result["townHallWeaponLevel"])

	role = result["role"]
	result["role"] = coc.roles[role]

	games_points = result["achievements"][31]["value"]
	games_points_pre = result["achievements"][31]["value"]
	games_points_visible = True
	
	star = result["warStars"]
	win0 = result["achievements"][12]["value"]
	win1 = result["versusBattleWins"]
	dont = result["donations"]
	care = result["achievements"][14]["value"] + result["achievements"][23]["value"]
	recv = result["donationsReceived"]
	tidy = result["achievements"][3]["value"]
	labs = coc.total_levels_raw(result)

	row0 = ["&nbsp;"]
	row1 = ["WS"]
	row2 = ["AW"]
	row3 = ["BW"]
	row4 = ["TD"]
	row5 = ["TR"]
	row6 = ["OR"]
	row7 = ["LV"]

	db = sqlite3.connect(Settings.db)
	db.row_factory = sqlite3.Row

	first_row = True
	count = 0
	active = 0.0
	now = datetime.datetime.now()
	cursor = db.cursor()
	cursor.execute("""SELECT J.*, achievement3, achievement12, achievement14, achievement23, achievement31, %s FROM journal AS J 
						LEFT JOIN achievements AS A ON J.id = A.journal
						LEFT JOIN heroes AS H ON J.id = H.journal
						LEFT JOIN troops AS T ON J.id = T.journal
						LEFT JOIN spells AS S ON J.id = S.journal
						WHERE tag = '%s' AND J.dt > DATE('now', '-30 days')
						ORDER BY dt DESC""" % (coc.names_for_query, tag))
	for row in cursor:
		if count > 30 and games_points == games_points_pre:
			games_points_visible = False
		elif count < 40:
			games_points_pre = row["achievement31"]

		if first_row:
			first_row = False
			day_of_week = "Now"
		else:
			now = datetime.datetime.strptime(row["dt"], "%Y-%m-%d %H:%M:%S")
			now += datetime.timedelta(hours=9)
			day_of_week = now.strftime("%a")

		star_diff = star - row["war_stars"]
		win0_diff = win0 - row["achievement12"]
		win1_diff = win1 - row["versus_battle_wins"]
		dont_diff = dont - row["troops_donated"];
		care_diff = care - (row["achievement14"] + row["achievement23"]);
		recv_diff = recv - row["troops_received"];
		tidy_diff = tidy - row["achievement3"];
		labs_new = coc.total_levels(row)
		labs_diff = labs - labs_new

		if recv_diff < 0:
			recv_diff = 0

		if star_diff > 0:
			active += 1
		if win0_diff > 0:
			active += 1
		if win1_diff > 0:
			active += 1
		if dont_diff > 0 or care_diff > 0:
			active += 1
		if recv_diff > 0:
			active += 1
		if tidy_diff > 0:
			active += 1
		if labs_diff > 0:
			active += 1

		row0.append(day_of_week)
		row1.append(star_diff)
		row2.append(win0_diff)
		row3.append(win1_diff)
		if dont_diff >= care_diff:
				row4.append(dont_diff)
		else:
				row4.append(care_diff)
		row5.append(recv_diff)
		row6.append(tidy_diff)
		row7.append(labs_diff)

		star = row["war_stars"]
		win0 = row["achievement12"]
		win1 = row["versus_battle_wins"]
		dont = row["troops_donated"]
		care = row["achievement14"] + row["achievement23"]
		recv = row["troops_received"]
		tidy = row["achievement3"]
		labs = labs_new

		count += 1

	if (datetime.datetime.now() - now).days < 27:
		dont_diff = dont - 0
		recv_diff = recv - 0

		row0.append("")
		row1.append("")
		row2.append("")
		row3.append("")
		row4.append(dont_diff)
		row5.append(recv_diff)
		row6.append("")
		row7.append("")

		if dont_diff > 0:
			active += 1
		if recv_diff > 0:
			active += 1

		count += 1

	result["row0"] = row0
	result["row1"] = row1
	result["row2"] = row2
	result["row3"] = row3
	result["row4"] = row4
	result["row5"] = row5
	result["row6"] = row6
	result["row7"] = row7
	result["townHall"] = town_hall
	if games_points_visible == True:
			result["gamesPoints"] = games_points - games_points_pre
        else:
			result["gamesPoints"] = games_points
	result["count"] = count
	result["active"] = active / count

	return result
