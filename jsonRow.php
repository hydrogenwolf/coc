<?php

require_once('globals.php');
require_once('db.php');

header('Content-Type: application/json');

date_default_timezone_set('Asia/Seoul');

if (isset($_GET['m']))	$mode = strtoupper($_GET['m']);
if(empty($mode))	$mode = "DIFF";

if (isset($_GET['t']))	$tag = strtoupper($_GET['t']);
if(empty($tag))	$tag = "8VYGJPCJ";
$tag = '#' . $tag;
$tag_url = substr($tag, 1);

$result = new stdClass();

$data = curl("https://api.clashofclans.com/v1/players/" . urlencode($tag));

if(!isset($data["name"]))
{
	if(isset($data["message"]))
	{
		$result->message = $data["message"];
	}
	else
	{
		$result->message = "No response from API.";
	}
}
else
{
	$result = $data;

	if(!isset($result["league"]))
	{
		$result["iconUrl"] = "https://api-assets.clashofclans.com/leagues/72/e--YMyIexEQQhE4imLoJcwhYn6Uy8KqlgyY3_kFV6t4.png";
	}
	else
	{
		$result["iconUrl"] = "http://coc.xor.kr/images/town-hall-" . $result["townHallLevel"];
		if(isset($result["townHallWeaponLevel"]))
		{
			$weapon = $result["townHallWeaponLevel"];
			$result["iconUrl"] .= "-" . $weapon;
		}
		$result["iconUrl"] .= ".png";
	}

	$role = $result["role"];
	$result["role"] = $roles[$role];
	$result["gamesPoints"] = "";

	$row0 = array("&nbsp;");
	$row1 = array("WS");
	$row2 = array("AW");
	$row3 = array("BW");
	$row4 = array("TD");
	$row5 = array("TR");
	$row6 = array("OR");
	$row7 = array("LV");

	$star = $data["warStars"];
	$win0 = $data["attackWins"];
	$win1 = $data["versusBattleWins"];
	$dont = $data["donations"];
	$recv = $data["donationsReceived"];
	$tidy = $data["achievements"][3]["value"];
	$labs = total_levels_raw($data);

	$games_points = $data["achievements"][31]["value"];
	$games_points_pre = $data["achievements"][31]["value"];

	$first_row = TRUE;
	$count = 0;	$active = 0;
	$sql =<<< EOT
SELECT J.*, achievement3, achievement31, $names_for_query FROM journal AS J
LEFT JOIN achievements AS A ON J.id = A.journal
LEFT JOIN heroes AS H ON J.id = H.journal
LEFT JOIN troops AS T ON J.id = T.journal
LEFT JOIN spells AS S ON J.id = S.journal
WHERE tag = '$tag' AND J.dt > DATE('now', '-40 days') 
ORDER BY J.dt DESC

EOT;
	$adapter = $db->query($sql);
	while($row = $adapter->fetchArray(SQLITE3_ASSOC))
	{
		// Clan Games Points
		if (isset($row["achievement31"]))	$games_points_pre = $row["achievement31"];

		if($mode == "DIFF")
		{
			if ($first_row)
			{
				$first_row = FALSE;
				$dayOfWeek = "Now";
			}
			else
			{
				$dt = new DateTime($row["dt"]);
				$dt->modify('+9 hours');
				$dayOfWeek = $dt->format('D');
			}

			$star_diff = $star - $row["war_stars"];
			$win0_diff = $win0 - $row["attack_wins"];
			$win1_diff = $win1 - $row["versus_battle_wins"];
			$dont_diff = $dont - $row["troops_donated"];
			$recv_diff = $recv - $row["troops_received"];
			if ($row["achievement3"])
				$tidy_diff = $tidy - $row["achievement3"];
			else
				$tidy_diff = "";
			$labs_new = total_levels($row);
			if ($labs_new)
				$labs_diff = $labs - $labs_new;
			else
				$labs_diff = "";
			if($star_diff < 0)	$star_diff = 0;
			if($win0_diff < 0)	$win0_diff = 0;
			if($win1_diff < 0)	$win1_diff = 0;
			if($dont_diff < 0)	$dont_diff = 0;
			if($recv_diff < 0)	$recv_diff = 0;
			if($tidy_diff < 0)	$tidy_diff = 0;
			if($labs_diff < 0)	$labs_diff = 0;

			if ($star_diff != 0)	$active += 1;
			if ($win0_diff != 0)	$active += 1;
			if ($win1_diff != 0)	$active += 1;
			if ($dont_diff != 0)	$active += 1;
			if ($recv_diff != 0)	$active += 1;
			if ($tidy_diff != 0)	$active += 1;
			if ($labs_diff != 0)	$active += 1;

			$row0[] = $dayOfWeek;
			$row1[] = $star_diff;
			$row2[] = $win0_diff;
			$row3[] = $win1_diff;
			$row4[] = $dont_diff;
			$row5[] = $recv_diff;
			$row6[] = $tidy_diff;
			$row7[] = $labs_diff;

			$star = $row["war_stars"];
			$win0 = $row["attack_wins"];
			$win1 = $row["versus_battle_wins"];
			$dont = $row["troops_donated"];
			$recv = $row["troops_received"];
			$tidy = $row["achievement3"];
			$labs = $labs_new;
		}
		else
		{
			if ($first_row)
			{
				$first_row = FALSE;
				$dayOfWeek = "Now";

				$row0[] = $dayOfWeek;
				$row1[] = $star;
				$row2[] = $win0;
				$row3[] = $win1;
				$row4[] = $dont;
				$row5[] = $recv;
				$row6[] = $tidy;
				$row7[] = $labs;
			}

			$dt = new DateTime($row["dt"]);
			// 00시 데이터를 새날의 오전 데이터로 볼 것인가, 전날의 오후 데이터로 볼 것인가.
			// +9 대신 +8 사용: 전날의 오후 데이터로 처리해서 DIFF와 통일감.
			$dt->modify('+8 hours');
			$dayOfWeek = $dt->format('D');

			$row0[] = $dayOfWeek;
			$row1[] = $row["war_stars"];
			$row2[] = $row["attack_wins"];
			$row3[] = $row["versus_battle_wins"];
			$row4[] = $row["troops_donated"];
			$row5[] = $row["troops_received"];
			$row6[] = $row["achievement3"];
			$row7[] = total_levels($row);
		}

		$count += 1;
	}

	if($mode != "DIFF")
	{
		$result["gamesPoints"] = $games_points;
	}
	else
	{
		$result["gamesPoints"] = $games_points - $games_points_pre;

		// 최초 데이터 Fetch 전에 한 Donated/Received 표시. 신입 멤버 평가에 유용.
		if (!isset($dt) || (time() - $dt->getTimestamp())/(60*60*24) < 27)
		{
			$dont_diff = $dont - 0;
			$recv_diff = $recv - 0;

			$row0[] = "";
			$row1[] = "";
			$row2[] = "";
			$row3[] = "";
			$row4[] = $dont_diff;
			$row5[] = $recv_diff;
			$row6[] = "";
			$row7[] = "";

			if ($dont_diff != 0)	$active += 1;
			if ($recv_diff != 0)	$active += 1;

			$count += 1;
		}
	}

	$result["row0"] = $row0;
	$result["row1"] = $row1;
	$result["row2"] = $row2;
	$result["row3"] = $row3;
	$result["row4"] = $row4;
	$result["row5"] = $row5;
	$result["row6"] = $row6;
	$result["row7"] = $row7;

	$result["count"] = $count;
	$result["active"] = $active / $count;
}

echo json_encode($result);

?>
