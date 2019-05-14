<?php

ini_set('error_reporting', E_ALL);
ini_set('short_open_tag', FALSE);

header('Content-Type: text/html; charset=UTF-8');

if (isset($_GET['t']))	$tag = strtoupper($_GET['t']);
if(empty($tag))	$tag = "20L8UCRRC";
$tag = '#' . $tag;

$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6IjA0YjU0NDlhLWM4ZDEtNDQ3Zi1iZWMwLTU1ZjhiMTA5ZjA3ZSIsImlhdCI6MTU1MTA3NjU5Mywic3ViIjoiZGV2ZWxvcGVyL2ZiMzE0MTE0LTljY2ItODhmZC0yMTljLTc4MWIxMGU4NDMxNiIsInNjb3BlcyI6WyJjbGFzaCJdLCJsaW1pdHMiOlt7InRpZXIiOiJkZXZlbG9wZXIvc2lsdmVyIiwidHlwZSI6InRocm90dGxpbmcifSx7ImNpZHJzIjpbIjExMi4yMTcuMTI2LjYwIl0sInR5cGUiOiJjbGllbnQifV19.OfFyFHrRO2J8H5C_DOsiNZXq63l8WbXQLLNb7RPgFUFV49yFd7cy317bu9uesvV3FLZZIEErur_nka7EhRNljA";
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
if(!isset($data["name"]) && isset($data["message"]))
{
	die($data["message"]);
}

$name = $data["name"];
$total_members = $data["members"];
$member_list = $data["memberList"];

$template =<<< EOT
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>$total_members members - $name</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" type="text/css" href="/style.css">
<style>
/*
table.sheet tr:hover {
	cursor: pointer;
	font-weight: bold;
}
*/
table.sheet .rank-container {
	width: 80px;
	position: relative;
}
table.sheet .rank {
	position: absolute;
	top: 4px;
	left: 4px;
	font: bold 20px sans-serif;
	color: White;
	text-shadow: -2px -2px 4px Black;
}
</style>
<script src="/jquery-3.3.1.min.js"></script>
<script>
$(document).ready(function() {
	/*
	$(".clickable-row").click(function() {
		window.location = $(this).data("href");
	});
	*/

	$("#check").click(function() {
		if ($(this).css("opacity") == "1") {
			$(this).css("opacity", "0.3");
			$("table.sheet td:first-child").each(function() {
				$(this).html('<input type="checkbox">');
			});

			$('table.sheet :checkbox').change(function() {
				var tr = $(this).closest('tr');
				if (this.checked) {
					tr.css("background-color", "DarkKhaki");
				} else {
					if(tr.index() % 2) {
						tr.css("background-color", "WhiteSmoke");
					} else {
						tr.css("background-color", "White");
					}
				}
			});
		}
		else {
			$(this).css("opacity", "1");
			$("table.sheet td:first-child").each(function() {
				$(this).html('');

				var tr = $(this).closest('tr');
				if(tr.index() % 2) {
					tr.css("background-color", "WhiteSmoke");
				} else {
					tr.css("background-color", "White");
				}
			});
		}
	});
});
</script>
</head>
<body>

%body%

</body>
</html>

EOT;

$body =<<< EOT
<h1> $name </h1>

<table class="sheet">
<tr> 
<th style="text-align:left; padding:0 10px;"><img id="check" src="/images/check-mark.png" alt="Check Mark" width="32"></th> <th colspan="3" style="text-align:right; padding:10px;"> $total_members members </th>
</tr>

EOT;

foreach ($member_list as $member)
{
	$name = htmlspecialchars($member["name"]);
	$tag = substr($member["tag"], 1);
	$donated = $member["donations"];
	$received = $member["donationsReceived"];
	$league = $member["league"]["name"];
	$league_icon = $member["league"]["iconUrls"]["small"];
	$clan_rank = $member["clanRank"];

	$body .=<<< EOT
<tr class="clickable-row" data-href="/player/$tag">
<td></td> <td><div class="rank-container"><div><img src="$league_icon" alt="$league"></div><div class="rank">$clan_rank</div></div></td> <td>$name</td> <td><a href="/player/$tag"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path style="fill:Gainsboro;" d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.568 18.005l-1.414-1.415 4.574-4.59-4.574-4.579 1.414-1.416 5.988 5.995-5.988 6.005z"/></svg></a></td> 
</tr>

EOT;
}

$body .= <<< EOT
</table>

EOT;

echo str_replace('%body%', $body, $template);

?>
