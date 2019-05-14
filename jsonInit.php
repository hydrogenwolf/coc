<?php

require_once('globals.php');
require_once('db.php');

header('Content-Type: application/json');

date_default_timezone_set('Asia/Seoul');

$mode = "";
if (isset($_GET['m']))	$mode = strtoupper($_GET['m']);

if (isset($_GET['t']))	$tag = strtoupper($_GET['t']);
if(empty($tag))	$tag = "20L8UCRRC";
$tag = '#' . $tag;

$result = new stdClass();

$data = curl("https://api.clashofclans.com/v1/clans/" . urlencode($tag));

if(!isset($data["memberList"]))
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

	$members = $result["memberList"];
	for($i=0; $i<count($members); $i++)
	{
		$member = $members[$i];

		$role = $member["role"];
		$result["memberList"][$i]["role"] = $roles[$role];
		$count = 0;	$active = 0;

		$row0 = array("&nbsp;");
		$row1 = array("WS");
		$row2 = array("AW");
		$row3 = array("BW");
		$row4 = array("TD");
		$row5 = array("TR");
		$row6 = array("OR");
		$row7 = array("LV");

		// Fetch 전인 신규 멤버는 아래 데이터가 없기 때문에 기본 값 지정
		$town_hall = 0;
		$games_points = 0;
		$games_points_pre = 0;

		$first_row = TRUE;
		$tag = $member["tag"];
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
			$dt = new DateTime($row["dt"]);
			$dt->modify('+9 hours');
			$dayOfWeek = $dt->format('D');

			if($first_row)
			{
				$first_row = FALSE;

				$town_hall = $row["town_hall"];
				$games_points = $row["achievement31"];
				$games_points_pre = $row["achievement31"];
			}
			else
			{
				if (isset($row["achievement31"]))
				{
					$games_points_pre = $row["achievement31"];
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
				$tl = total_levels($row);
				if ($tl)
					$labs_diff = $labs - $tl;
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
			}

			$star = $row["war_stars"];
			$win0 = $row["attack_wins"];
			$win1 = $row["versus_battle_wins"];
			$dont = $row["troops_donated"];
			$recv = $row["troops_received"];
			$tidy = $row["achievement3"];
			$labs = total_levels($row);

			$count += 1;
		}

		$games_points = $games_points - $games_points_pre;

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
		}

		$result["memberList"][$i]["row0"] = $row0;
		$result["memberList"][$i]["row1"] = $row1;
		$result["memberList"][$i]["row2"] = $row2;
		$result["memberList"][$i]["row3"] = $row3;
		$result["memberList"][$i]["row4"] = $row4;
		$result["memberList"][$i]["row5"] = $row5;
		$result["memberList"][$i]["row6"] = $row6;
		$result["memberList"][$i]["row7"] = $row7;
		$result["memberList"][$i]["townHallLevel"] = $town_hall;
		$result["memberList"][$i]["gamesPoints"] = $games_points;
		$result["memberList"][$i]["count"] = $count;
		$result["memberList"][$i]["active"] = $active;
	}
}

if ($mode == "INACTIVE")
{
	usort($result["memberList"], function($a, $b) {
		return $a['active'] / $a['count'] <=> $b['active'] / $b['count'];
	});
}
else if ($mode == "ACTIVE")
{
	usort($result["memberList"], function($a, $b) {
		return $b['active'] / $b['count'] <=> $a['active'] / $a['count'];
	});
}
else if ($mode == "NEW")
{
	usort($result["memberList"], function($a, $b) {
		return $a['count'] <=> $b['count'];
	});
}

echo json_encode($result);

?>
