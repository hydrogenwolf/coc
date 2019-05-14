<?php

ini_set('error_reporting', E_ALL);
ini_set('short_open_tag', FALSE);

header('Content-Type: text/html; charset=UTF-8');

require_once('globals.php');
require_once 'db.php';

$offensives = array($hero_names, $troop_names, $spell_names);

$names_for_query = "";
foreach($offensives as $offensive)
{
	foreach($offensive as $name)
	{
		if(strlen($names_for_query) > 0)	$names_for_query .= ", ";
		$names_for_query .= "[$name]";
	}
}

date_default_timezone_set('Asia/Seoul');

if (isset($_GET['t']))	$tag = strtoupper($_GET['t']);
if(empty($tag))	$tag = "20L8UCRRC";
$tag = '#' . $tag;

$data = curl("https://api.clashofclans.com/v1/clans/" . urlencode($tag));

if(!isset($data["name"]) && isset($data["message"]))
{
	die($data["message"]);
}

$clan_name = $data["name"];
$member_list = $data["memberList"];
$total_members = $data["members"];

$template =<<< EOT
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>$total_members members - $clan_name</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style>
table {
	border-collapse: collapse;
}
td {
	text-align: center;
	padding: 4px;
	border: 1px solid DarkGreen;
	font-family: "Consolas", "Menlo", monospace;
	font-size: 12px;
}
table div.rank-container {
	width: 120px;
	position: relative;
}
table div.rank {
	position: absolute;
	top: 4px;
	left: 4px;
	font: bold 1.5em sans-serif;
	color: White;
	text-shadow: 2px 2px 4px Black;
}
table div.league {
	cursor: pointer;
}
table div.name {
	margin: 6px 3px;
	font-weight: bold;
	font-size: 1.1em;
}
table div.role {
	font-size: 0.9em;
	color: DimGray;
}
table div.townhall a {
	font-size: 0.9em;
	color: DimGray;
	text-decoration: none;
}
table div.points {
}
table .full {
	display: none;
}
</style>
<script src="/jquery-3.3.1.min.js"></script>
<script>
$(document).ready(function() {
	$("td").each(function() {
		if (this.className == 'diff') {
			if (parseInt($(this).text()) == 0) {
				var styles = {
					backgroundColor : 'White',
					color: 'White'
				};
				$(this).css(styles);
			}
			else if (parseInt($(this).text()) > 0) {
				var styles = {
					backgroundColor : 'DarkSeaGreen',
					color: 'Black'
				};
				$(this).css(styles);
			} else {
			/*
				var styles = {
					backgroundColor : 'White',
					color: 'LightGray'
				};
				$(this).css(styles);
			*/
			}
		}
	});

	$("#full").click(function() {
		$(".diff").hide();
		$(".full").show();
	});

	$("#diff").click(function() {
		$(".full").hide();
		$(".diff").show();
	});

	$(".remove").click(function() {
		var trs = new Array($(this).closest('tr'));
		for(i=0; i<7; i++)
		{
			trs.push(trs[i].next('tr'));
		}
		for(i=7; i>=0; i--)
		{
			trs[i].remove();
		}
	});
});
</script>
</head>
<body>

<p>
<button id="full" style="width:130px;">Full</button>
<button id="diff" style="width:130px;">Differential</button>
</p>

%body%

</body>
</html>

EOT;

$body =<<< EOT
<table id="sheet">

EOT;

foreach ($member_list as $member)
{
	$tag = $member["tag"];
	$tag_url = substr($tag, 1);

	// 한번도 Fetch한 적이 없는 클랜을 조회하는 경우를 고려해서 DB fetchArray 루프 밖에 위치
	$name = htmlspecialchars($member["name"]);
	$clan_rank = $member["clanRank"];
	$league = $member["league"]["name"];
	$league_icon = $member["league"]["iconUrls"]["small"];
	$role = $member["role"];
	$role_human = $roles[$role];
	$row0 = "<td>    </td>";
	$row1 = "<td> WS </td>";
	$row2 = "<td> AW </td>";
	$row3 = "<td> BW </td>";
	$row4 = "<td> TD </td>";
	$row5 = "<td> TR </td>";
	$row6 = "<td> OR </td>";
	$row7 = "<td> LV </td>";
	$townhall = "";

	$first_row = TRUE;
	$games_points = 0;
	$games_points_pre = 0;
	$timestamp = 0;
	$sql =<<< EOT
SELECT J.*, achievement3, achievement31, $names_for_query FROM journal AS J
	LEFT JOIN achievements AS A ON J.id = A.journal
	LEFT JOIN heroes AS H ON J.id = H.journal
	LEFT JOIN troops AS T ON J.id = T.journal
	LEFT JOIN spells AS S ON J.id = S.journal
	WHERE tag = '$tag' AND J.dt > DATE('now', '-28 days') 
	ORDER BY J.dt DESC

EOT;
	$adapter = $db->query($sql);
	while($row = $adapter->fetchArray(SQLITE3_ASSOC))
	{
		$timestamp = strtotime($row["dt"] . ' UTC');
		$dayOfWeek = date('D', $timestamp);

		if($first_row)
		{
			$first_row = FALSE;

			$townhall = "TH" . $row["town_hall"];
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
			$row0 .= "<td class='diff'>$dayOfWeek</td>";
			$row1 .= "<td class='diff'>$star_diff</td>";
			$row2 .= "<td class='diff'>$win0_diff</td>";
			$row3 .= "<td class='diff'>$win1_diff</td>";
			$row4 .= "<td class='diff'>$dont_diff</td>";
			$row5 .= "<td class='diff'>$recv_diff</td>";
			$row6 .= "<td class='diff'>$tidy_diff</td>";
			$row7 .= "<td class='diff'>$labs_diff</td>";
		}

		$star = $row["war_stars"];
		$win0 = $row["attack_wins"];
		$win1 = $row["versus_battle_wins"];
		$dont = $row["troops_donated"];
		$recv = $row["troops_received"];
		$tidy = $row["achievement3"];
		$labs = total_levels($row);
		$row0 .= "<td class='full'>$dayOfWeek</td>";
		$row1 .= "<td class='full'>$star</td>";
		$row2 .= "<td class='full'>$win0</td>";
		$row3 .= "<td class='full'>$win1</td>";
		$row4 .= "<td class='full'>$dont</td>";
		$row5 .= "<td class='full'>$recv</td>";
		$row6 .= "<td class='full'>$tidy</td>";
		$row7 .= "<td class='full'>$labs</td>";
	}

	// 신규 멤버에 대해 최초 데이터 Fetch 전에 한 Donation 등을 표시
	$datediff = time() - $timestamp;
	if ($datediff/(60*60*24) < 27)
	{
		$win0_diff = $win0 - 0;
		$dont_diff = $dont - 0;
		$recv_diff = $recv - 0;

		$row0 .= "<td class='diff'>          </td> </tr>";
		$row1 .= "<td class='diff'>          </td> </tr>";
		$row2 .= "<td class='diff'>$win0_diff</td> </tr>";
		$row3 .= "<td class='diff'>          </td> </tr>";
		$row4 .= "<td class='diff'>$dont_diff</td> </tr>";
		$row5 .= "<td class='diff'>$recv_diff</td> </tr>";
		$row6 .= "<td class='diff'>          </td> </tr>";
		$row7 .= "<td class='diff'>          </td> </tr>";
	}

	$games_points_diff = $games_points - $games_points_pre;
	if($games_points_diff == 0)	$games_points_diff = "";

	$row0 =<<< EOT
<tr>
<td rowspan='8'>
	<div class="rank-container">
		<div class="rank">
			$clan_rank
		</div>
		<div class="league">
			<img src='$league_icon' alt='$league' class='remove' />
		</div>
	</div>
	<div class="name">
		$name
	</div>
	<div class="role">
		$role_human
	</div>
	<div class="townhall">
		<a href='/player/$tag_url'>$townhall</a>
	</div>
	<div class="points full">
		$games_points
	</div>
	<div class="points diff">
		$games_points_diff
	</div>
</td>

EOT
. $row0;
	$row1 = "<tr> " . $row1;
	$row2 = "<tr> " . $row2;
	$row3 = "<tr> " . $row3;
	$row4 = "<tr> " . $row4;
	$row5 = "<tr> " . $row5;
	$row6 = "<tr> " . $row6;
	$row7 = "<tr> " . $row7;

	$body .=<<< EOT
$row0
$row1
$row2
$row3
$row4
$row5
$row6
$row7

EOT;
}
$db->close();

$body .=<<< EOT
</table>

EOT;

echo str_replace('%body%', $body, $template);

?>
