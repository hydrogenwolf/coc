import sqlite3
import urllib
import json
import datetime

import sys, os
sys.path.append(os.path.dirname(__file__))
from settings import *
import coc

def application(environ, start_response):
	tag = "#20L8UCRRC"	# NAMU
	response = json.dumps(sheet(tag))

	response_headers = [('Content-type', 'application/json'), ('Content-Length', str(len(response)))]
	start_response('200 OK', response_headers)
	return [response]

def sheet(tag):
	db = sqlite3.connect(Settings.db)
	db.row_factory = sqlite3.Row

	url = "https://api.clashofclans.com/v1/clans/" + urllib.quote_plus(tag)
	result = coc.curl(url, Settings.token)

	if "memberList" not in result:
		return result

	for i in range(len(result["memberList"])):
		member = result["memberList"][i]

		role = member["role"]
		result["memberList"][i]["role"] = coc.roles[role]

		dont = member["donations"]
		recv = member["donationsReceived"]

		row0 = ["&nbsp;"]
		row1 = ["WS"]
		row2 = ["AW"]
		row3 = ["BW"]
		row4 = ["TD"]
		row5 = ["TR"]
		row6 = ["OR"]
		row7 = ["LV"]
		
		first_row = True
		tag = member["tag"];
		now = datetime.datetime.now()
		cursor = db.cursor()
		cursor.execute("""SELECT J.*, achievement3, achievement12, achievement14, achievement23, achievement31, %s FROM journal AS J 
							LEFT JOIN achievements AS A ON J.id = A.journal
							LEFT JOIN heroes AS H ON J.id = H.journal
							LEFT JOIN troops AS T ON J.id = T.journal
							LEFT JOIN spells AS S ON J.id = S.journal
							WHERE tag = '%s' AND J.dt > DATE('now', '-40 days')
							ORDER BY dt DESC""" % (coc.names_for_query, tag))
		for row in cursor:
			now = datetime.datetime.strptime(row["dt"], "%Y-%m-%d %H:%M:%S")
			now += datetime.timedelta(hours=9)
			day_of_week = now.strftime("%a")

			if first_row:
				first_row = False

				star = row["war_stars"]
				win0 = row["achievement12"]
				win1 = row["versus_battle_wins"]
				dont = row["troops_donated"]
				care = row["achievement14"] + row["achievement23"]
				recv = row["troops_received"]
				tidy = row["achievement3"]
				labs = coc.total_levels(row)
			else:
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

		result["memberList"][i]["row0"] = row0
		result["memberList"][i]["row1"] = row1
		result["memberList"][i]["row2"] = row2
		result["memberList"][i]["row3"] = row3
		result["memberList"][i]["row4"] = row4
		result["memberList"][i]["row5"] = row5
		result["memberList"][i]["row6"] = row6
		result["memberList"][i]["row7"] = row7

	return result
