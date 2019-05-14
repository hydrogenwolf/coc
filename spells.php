<?php

ini_set('error_reporting', E_ALL);
ini_set('short_open_tag', FALSE);

header('Content-Type: text/html; charset=UTF-8');

require_once('globals.php');

$target = "spells";
$names = $spell_names;

$names_for_query = "";
foreach($names as $name)
{
	if(strlen($names_for_query) > 0)	$names_for_query .= ", ";
	$names_for_query .= "[$name]";
}

date_default_timezone_set('Asia/Seoul');

if (isset($_GET['t']))	$tag = strtoupper($_GET['t']);
if(empty($tag))	$tag = "20L8UCRRC";
$tag = '#' . $tag;

$url = "https://api.clashofclans.com/v1/clans/" . urlencode($tag);

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

$data = json_decode($result, true);
$total_members = $data["members"];
$member_list = $data["memberList"];

$template =<<< EOT
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>$target - namu</title>
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
h4.name {
	margin: 0;
}
p.role {
	margin: 0;
	font-size: 0.9em;
}
div.rank-container {
	width: 120px;
	position: relative;
}
div.rank {
	position: absolute;
	top: 4px;
	left: 14px;
	font: bold 2em sans-serif;
	color: White;
	text-shadow: -2px -2px 4px Black;
}
.full {
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
});
</script>
</head>
<body>

<p>
<button id="full">Full</button>
<button id="diff">Differential</button>
</p>

%body%

</body>
</html>

EOT;

include 'db.php';

$body = '<table id="sheet">';

foreach ($member_list as $member)
{
	$tag = $member["tag"];
	$tag_url = substr($tag, 1);

	$rows = array();
	$values = array();

	$first_row = TRUE;
	$games_points = 0;
	$games_points_pre = 0;
	$sql =<<< EOT
SELECT J.*, achievement31, $names_for_query FROM journal AS J
	LEFT JOIN achievements AS A ON J.id = A.journal
	LEFT JOIN $target AS T ON J.id = T.journal 
	WHERE tag = '$tag' AND J.dt > DATE('now', '-28 days') 
	ORDER BY J.dt DESC

EOT;
	$adapter = $db->query($sql);
	while($r = $adapter->fetchArray(SQLITE3_ASSOC))
	{
		$dayOfWeek = date('D', strtotime($r["dt"] . ' UTC'));

		if($first_row)
		{
			$player_name = $member["name"];
			$league = $member["league"]["name"];
			$league_icon = $member["league"]["iconUrls"]["small"];
			$role = $member["role"];
			$role_human = $roles[$role];
			$clan_rank = $member["clanRank"];

			$townhall = $r["town_hall"];
			$games_points = $r["achievement31"];
			$games_points_pre = $r["achievement31"];
			for($i=0; $i<count($names); $i++)
			{
				$name = $names[$i];

				if(isset($r[$name]))	$values[$i] = $r[$name];
				else			$values[$i] = "";
			}

			$rows[0] = "<td>    </td> <td class='full'>$dayOfWeek</td>";
			for($i=0; $i<count($names); $i++)
			{
				$index = $i + 1;
				$rows[$index] = "<td>$names[$i]</td> <td class='full'>$values[$i]</td>";
			}

			$first_row = FALSE;
		}
		else
		{
			$diffs = array();
			for($i=0; $i<count($names); $i++)
			{
				$name = $names[$i];

				if(isset($r[$name]))	$diffs[$i] = $values[$i] - $r[$name];
				else $diffs[$i] = "";
			}
			$rows[0] .= "<td class='diff'>$dayOfWeek</td>";
			for($i=0; $i<count($names); $i++)
			{
				$index = $i + 1;
				$rows[$index] .= "<td class='diff'>$diffs[$i]</td>";
			}

			for($i=0; $i<count($names); $i++)
			{
				$name = $names[$i];

				if(isset($r[$name]))	$values[$i] = $r[$name];
				else			$values[$i] = "";
			}
			$rows[0] .= "<td class='full'>$dayOfWeek</td>";
			for($i=0; $i<count($names); $i++)
			{
				$index = $i + 1;
				$rows[$index] .= "<td class='full'>$values[$i]</td>";
			}

			if (isset($row["achievement31"]))
			{
				$games_points_pre = $row["achievement31"];
			}
		}
	}

	$rowspan = count($names) + 1;
	$games_points_diff = $games_points - $games_points_pre;
	if($games_points_diff == 0)	$games_points_diff = "";

	$rows[0] =<<< EOT
<td rowspan='$rowspan'>
	<div class='rank-container'>
		<div class='rank'>
			$clan_rank
		</div>
		<div>
			<a href='/player/$tag_url'><img src='$league_icon' alt='$league' /></a>
			<h4 class='name'>$player_name</h4>
			<p class='role'>$role_human</p>
		</div>
	</div>
</td>
<td rowspan='$rowspan'>
	<h4 class='name'>TH$townhall</h4>
	<p class='role full'>$games_points</p>
	<p class='role diff'>$games_points_diff</p>
</td>

EOT
. $rows[0];

	// 드디어 index와 i가 같이지는 순간	
	for($i=0; $i<=count($names); $i++)
	{
		$body .= "<tr> $rows[$i] </tr>\n";
	}
}
$db->close();

$body .=<<< EOT
</table>

EOT;

echo str_replace('%body%', $body, $template);

?>
