<?php

ini_set('error_reporting', E_ALL);
ini_set('short_open_tag', FALSE);

header('Content-Type: text/html; charset=UTF-8');

date_default_timezone_set('Asia/Seoul');

if (isset($_GET['t']))	$tag = strtoupper($_GET['t']);
if(empty($tag))	$tag = "8VYGJPCJ";
$tag = '#' . $tag;

$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6IjA0YjU0NDlhLWM4ZDEtNDQ3Zi1iZWMwLTU1ZjhiMTA5ZjA3ZSIsImlhdCI6MTU1MTA3NjU5Mywic3ViIjoiZGV2ZWxvcGVyL2ZiMzE0MTE0LTljY2ItODhmZC0yMTljLTc4MWIxMGU4NDMxNiIsInNjb3BlcyI6WyJjbGFzaCJdLCJsaW1pdHMiOlt7InRpZXIiOiJkZXZlbG9wZXIvc2lsdmVyIiwidHlwZSI6InRocm90dGxpbmcifSx7ImNpZHJzIjpbIjExMi4yMTcuMTI2LjYwIl0sInR5cGUiOiJjbGllbnQifV19.OfFyFHrRO2J8H5C_DOsiNZXq63l8WbXQLLNb7RPgFUFV49yFd7cy317bu9uesvV3FLZZIEErur_nka7EhRNljA";
$url = "https://api.clashofclans.com/v1/players/" . urlencode($tag);

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
if(!isset($data["name"]) && isset($data["message"]))
{
	die($data["message"]);
}

// For Leaving Members
$clan = "";
$role = "";

$name = htmlspecialchars($data["name"]);
$exp_level = $data["expLevel"];
$trophies = $data["trophies"];
if (isset($data["clan"]["name"]))	$clan = $data["clan"]["name"];
$war_stars = $data["warStars"];
$best_trophies = $data["bestTrophies"];
$donations = $data["donations"];
$donations_received = $data["donationsReceived"];
$attack_wins = $data["attackWins"];
$defense_wins = $data["defenseWins"];
$versus_battle_wins = $data["versusBattleWins"];
$town_hall_level = $data["townHallLevel"];
if(isset($data["townHallWeaponLevel"]))
{
	$weapon = $data["townHallWeaponLevel"];
	$town_hall_level .= "-" . $weapon;
}

if (isset($data["league"]))
{
	$league = $data["league"]["name"];
	$league_icon = $data["league"]["iconUrls"]["small"];
}
else
{
	$league = "Unranked";
	$league_icon = "https://api-assets.clashofclans.com/leagues/72/e--YMyIexEQQhE4imLoJcwhYn6Uy8KqlgyY3_kFV6t4.png";
}

$roles = array(
	"leader" => "Leader",
	"coLeader" => "Co-leader",
	"admin" => "Elder",
	"member" => "Member",
);
if (isset($data["role"]))
{
	$role = $data["role"];
	$role = $roles[$role];
}

foreach($data['heroes'] as $hero)
{
	switch($hero['name'])
	{
		case 'Barbarian King':
		$barbarian_king = $hero['level'];
		break;

		case 'Archer Queen':
		$archer_queen = $hero['level'];
		break;

		case 'Grand Warden':
		$grand_warden = $hero['level'];
		break;

		case 'Battle Machine':
		$battle_machine = $hero['level'];
		break;
	}
}

if (isset($barbarian_king))
{
	$barbarian_king_title = 'BK';
}
else
{
	$barbarian_king = '';
	$barbarian_king_title = '';
}
if (isset($archer_queen))
{
	$archer_queen_title = 'AQ';
}
else
{
	$archer_queen = '';
	$archer_queen_title = '';
}
if (isset($grand_warden))
{
	$grand_warden_title = 'GW';
}
else
{
	$grand_warden = '';
	$grand_warden_title = '';
}

$template =<<< EOT
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>$name - $clan</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" type="text/css" href="/style.css">
<style>
table.sheet th {
	padding: 10px 5px;
}
table.sheet td {
	padding: 10px 5px;
}
</style>
<script src="/jquery-3.3.1.min.js"></script>
<script>
$(document).ready(function() {
	//setInterval(rotateTownHallImage, 5000);
});
function rotateTownHallImage()
{
	var imagePath = '/images/';
	var el = $('#town_hall_image');
	var filename = el.attr('src').replace(imagePath, '');
	var townhall = filename.substring(10).substring(0, 2);

	if (townhall == '12')
	{
		var tail = filename.substring(10).substring(3).substring(0, 1);
		if (tail == 'p')
		{
			filename = 'town-hall-12-2.png';
			el.attr('src', imagePath + filename);
		}
		else if (tail == 5)
		{
			filename = 'town-hall-12.png';
			el.attr('src', imagePath + filename);
		}
		else 
		{
			tail = parseInt(tail) + 1;
			filename = 'town-hall-12-' + tail + '.png';
			el.attr('src', imagePath + filename);
		}
	}
}
</script>
</head>
<body>

%body%

</body>
</html>

EOT;

$body =<<< EOT
<table style="text-align:center;">
<col style="width:140px;">
<col style="width:80px;">
<col style="width:80px;">
<col style="width:80px;">
<tr style="height:50px;">
<td rowspan="4" style="padding:10px;"><img id="town_hall_image" src="/images/town-hall-$town_hall_level.png" alt="Town Hall $town_hall_level" width="120" /></td> <td style="font-size:x-large; font-weight:bold; vertical-align:bottom;" colspan="3">$name</td>
</tr>
<tr style="vertical-align:top; height:15px;">
<td style="font-size:small; padding-bottom:10px;" colspan="3">$role</td>
</tr>
<tr style="vertical-align:bottom; height:30px;">
<td style="text-align:right;">$barbarian_king_title</td>
<td>$archer_queen_title</td>
<td style="text-align:left;">$grand_warden_title</td>
</tr>
<tr style="vertical-align:top; height:30px;">
<td style="text-align:right;">$barbarian_king</td>
<td>$archer_queen</td>
<td style="text-align:left;">$grand_warden</td>
</tr>
</table>

EOT;

include 'db.php';

$body .=<<< EOT
<table class="sheet">
<col style="width:15%">
<col style="width:17%">
<col style="width:17%">
<col style="width:17%">
<col style="width:17%">
<col style="width:17%">
<tr>
<th> </th>
<th>WS</th>
<th>AW</th>
<th>BW</th>
<th>TD</th>
<th>TR</th>
</tr>
<tr>
<td> </td>
<td>$war_stars</td>
<td>$attack_wins</td>
<td>$versus_battle_wins</td>
<td>$donations</td>
<td>$donations_received</td>
</tr>

EOT;

$diff = '';

$sql = "SELECT * FROM journal WHERE tag = '$tag' AND dt > DATE('now', '-28 days') ORDER BY dt";
$adapter = $db->query($sql);
while($row = $adapter->fetchArray(SQLITE3_ASSOC))
{
	$dateTime = new DateTime($row["dt"]);
	$dateTime->add(new DateInterval('PT9H'));	// UTC -> GMT9
	$dateTime->sub(new DateInterval('PT12H'));
	$dayOfWeek = $dateTime->format('D');

	if(isset($star) && isset($win0) && isset($win1) && isset($dont) && isset($recv))
	{
		$star_diff = $row["war_stars"] - $star;
		$win0_diff = $row["attack_wins"] - $win0;
		$win1_diff = $row["versus_battle_wins"] - $win1;
		$dont_diff = $row["troops_donated"] - $dont;
		$recv_diff = $row["troops_received"] - $recv;
		if($star_diff <= 0)	$star_diff = "";
		if($win0_diff <= 0)	$win0_diff = "";
		if($win1_diff <= 0)	$win1_diff = "";
		if($dont_diff <= 0)	$dont_diff = "";
		if($recv_diff <= 0)	$recv_diff = "";

		$diff =<<< EOT
<tr>
<td>$dayOfWeek</td>
<td class="diff">$star_diff</td>
<td class="diff">$win0_diff</td>
<td class="diff">$win1_diff</td>
<td class="diff">$dont_diff</td>
<td class="diff">$recv_diff</td>
</tr>

EOT
. $diff;
	}

	$star = $row["war_stars"];
	$win0 = $row["attack_wins"];
	$win1 = $row["versus_battle_wins"];
	$dont = $row["troops_donated"];
	$recv = $row["troops_received"];
}

$body .=<<< EOT
$diff
</table>

EOT;

echo str_replace('%body%', $body, $template);

?>
