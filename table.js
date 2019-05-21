
var refreshedRow = 0;

function initSheet(data) {
	if (!data.members)
	{
		if (data.message)	document.body.innerHTML = data.message;

		return;
	}

	document.title = data.members + " members - " + data.name;

	var tbody = document.createElement("tbody");

	for (i=0; i<data.memberList.length; i++)
	{
		var member = data.memberList[i];
		var tag = member.tag.substring(1);
		
		var td0 = setFirstCell(member);
		var tr0 = document.createElement("tr");
		tr0.id = tag;
		tr0.className = "row";
		tr0.setAttribute("rank", member.clanRank);
		tr0.appendChild(td0);

		var tds0 = new Array();
		var tds1 = new Array();
		var tds2 = new Array();
		var tds3 = new Array();
		var tds4 = new Array();
		var tds5 = new Array();
		var tds6 = new Array();
		var tds7 = new Array();
		for (j=0; j<member.row0.length; j++)
		{
			tds0[j] = document.createElement("td");
			tds1[j] = document.createElement("td");
			tds2[j] = document.createElement("td");
			tds3[j] = document.createElement("td");
			tds4[j] = document.createElement("td");
			tds5[j] = document.createElement("td");
			tds6[j] = document.createElement("td");
			tds7[j] = document.createElement("td");

			if (j != 0)
			{
				if (member.row1[j] > 0)
					tds1[j].className = "active";
				else
					tds1[j].className = "hide";
				if (member.row2[j] > 0)
					tds2[j].className = "active";
				else
					tds2[j].className = "hide";
				if (member.row3[j] > 0)
					tds3[j].className = "active";
				else
					tds3[j].className = "hide";
				if (member.row4[j] > 0)
					tds4[j].className = "active";
				else
					tds4[j].className = "hide";
				if (member.row5[j] > 0)
					tds5[j].className = "active";
				else
					tds5[j].className = "hide";
				if (member.row6[j] > 0)
					tds6[j].className = "active";
				else
					tds6[j].className = "hide";
				if (member.row7[j] > 0)
					tds7[j].className = "active";
				else
					tds7[j].className = "hide";
			}

			tds0[j].innerHTML = member.row0[j];
			tds1[j].innerHTML = member.row1[j];
			tds2[j].innerHTML = member.row2[j];
			tds3[j].innerHTML = member.row3[j];
			tds4[j].innerHTML = member.row4[j];
			tds5[j].innerHTML = member.row5[j];
			tds6[j].innerHTML = member.row6[j];
			tds7[j].innerHTML = member.row7[j];
		}

		var tr1 = document.createElement("tr");
		var tr2 = document.createElement("tr");
		var tr3 = document.createElement("tr");
		var tr4 = document.createElement("tr");
		var tr5 = document.createElement("tr");
		var tr6 = document.createElement("tr");
		var tr7 = document.createElement("tr");

		for (j=0; j<member.row0.length; j++)
		{
			tr0.appendChild(tds0[j]);
			tr1.appendChild(tds1[j]);
			tr2.appendChild(tds2[j]);
			tr3.appendChild(tds3[j]);
			tr4.appendChild(tds4[j]);
			tr5.appendChild(tds5[j]);
			tr6.appendChild(tds6[j]);
			tr7.appendChild(tds7[j]);
		}
		
		tbody.appendChild(tr0);
		tbody.appendChild(tr1);
		tbody.appendChild(tr2);
		tbody.appendChild(tr3);
		tbody.appendChild(tr4);
		tbody.appendChild(tr5);
		tbody.appendChild(tr6);
		tbody.appendChild(tr7);
	}

	var table = document.createElement("table");
	table.id = "sheet";
	table.appendChild(tbody);
	document.body.appendChild(table);
}

function clickRank() {
	removeChunk(this);
}

function removeChunk(item) {
	var trs = new Array();
	var tr = item.closest("tr");

	do {
		trs.push(tr);
		tr = tr.nextElementSibling;
	} while (tr && tr.className != "row")

	for(var i = trs.length - 1; i >= 0; i--) {
		trs[i].remove();
	}
}

function resetRow(tr) {
	var span = tr.cells[0].rowSpan;
	var trs = new Array();

	trs.push(tr);

	for(var i = 0; i < span - 1; i++)
	{
		tr = tr.nextElementSibling;
		trs.push(tr);
	}

	for(var i = span - 1; i > 0; i--)
	{
		trs[i].remove();
	}

	for (var i = trs[0].cells.length - 1; i > 0; i--)
	{
		trs[0].deleteCell(i);
	}

	trs[0].cells[0].rowSpan = "1";
}

function setFirstCell(data) {
	var img = document.createElement("img");
	img.className = "icon";
	if (data.townHall)
	{
		img.src = "http://coc.xor.kr/images/town-hall-" + data.townHall + ".png";
		img.alt = "Townhall " + data.townHall;
		img.height = 72;
	}
	else
	{
		img.src = data.league.iconUrls.small;
		img.alt = data.league.name;
	}
	var divLeague = document.createElement("div");
	divLeague.className = "league";
	divLeague.appendChild(img);
	var divRank = document.createElement("div");
	divRank.className = "rank";
	divRank.addEventListener("click", clickRank);
	if(data.clanRank)
		divRank.innerHTML = data.clanRank;
	var divGamesPoints = document.createElement("div");
	divGamesPoints.className = "points";
	if(data.gamesPoints)
	{
		var points = parseInt(data.gamesPoints);
	
		if (points > 4000)	points = 4000;
		divGamesPoints.innerHTML = points;
	}
	var divRankContainer = document.createElement("div");
	divRankContainer.className = "rank-container";
	divRankContainer.appendChild(divRank);
	divRankContainer.appendChild(divGamesPoints);
	divRankContainer.appendChild(divLeague);
	var txtName = document.createTextNode(data.name);
	var divName = document.createElement("div");
	divName.className = "name";
	divName.appendChild(txtName);
	var divRole = document.createElement("div");
	divRole.className = "role";
	divRole.innerHTML = data.role;

	var cell = document.createElement("td");
	cell.rowSpan = "8";
	cell.appendChild(divRankContainer);
	cell.appendChild(divName);
	cell.appendChild(divRole);

	return cell;
}

function refreshRow(data, mode) {
	var tds0 = new Array();
	var tds1 = new Array();
	var tds2 = new Array();
	var tds3 = new Array();
	var tds4 = new Array();
	var tds5 = new Array();
	var tds6 = new Array();
	var tds7 = new Array();

	for (i=0; i<data.row0.length; i++)
	{
		tds0[i] = document.createElement("td");
		tds1[i] = document.createElement("td");
		tds2[i] = document.createElement("td");
		tds3[i] = document.createElement("td");
		tds4[i] = document.createElement("td");
		tds5[i] = document.createElement("td");
		tds6[i] = document.createElement("td");
		tds7[i] = document.createElement("td");

		if (i != 0)
		{
			if (data.row1[i] > 0)
				tds1[i].className = "active";
			else
				tds1[i].className = "hide";
			if (data.row2[i] > 0)
				tds2[i].className = "active";
			else
				tds2[i].className = "hide";
			if (data.row3[i] > 0)
				tds3[i].className = "active";
			else
				tds3[i].className = "hide";
			if (data.row4[i] > 0)
				tds4[i].className = "active";
			else
				tds4[i].className = "hide";
			if (data.row5[i] > 0)
				tds5[i].className = "active";
			else
				tds5[i].className = "hide";
			if (data.row6[i] > 0)
				tds6[i].className = "active";
			else
				tds6[i].className = "hide";
			if (data.row7[i] > 0)
				tds7[i].className = "active";
			else
				tds7[i].className = "hide";
		}

		tds0[i].innerHTML = data.row0[i];
		tds1[i].innerHTML = data.row1[i];
		tds2[i].innerHTML = data.row2[i];
		tds3[i].innerHTML = data.row3[i];
		tds4[i].innerHTML = data.row4[i];
		tds5[i].innerHTML = data.row5[i];
		tds6[i].innerHTML = data.row6[i];
		tds7[i].innerHTML = data.row7[i];
	}

	var tbody = document.getElementById("sheet").getElementsByTagName("tbody")[0];
	var tag = data.tag.substring(1);
	for (var i = 0; i < tbody.rows.length; i++)
	{
		if(tag == tbody.rows[i].id)
		{
			data.clanRank = tbody.rows[i].getAttribute("rank");
			removeChunk(tbody.rows[i]);
			break;
		}
	}

	var placed = false;
	var place = 0;
	if (mode == "Inactive") {
		var value = data.active;
		for (var i = 0; i < tbody.rows.length; i++)
		{
			if (tbody.rows[i].className != "row")	continue;

			var v = tbody.rows[i].getAttribute("active");
			if (!v || value <= v)
			{
				place = i;
				placed = true;
				break;
			}
		}
	} else if (mode == "Active") {
		var value = data.active;
		for (var i = 0; i < tbody.rows.length; i++)
		{
			if (tbody.rows[i].className != "row")	continue;

			var v = tbody.rows[i].getAttribute("active");
			if (!v || value >= v)
			{
				place = i;
				placed = true;
				break;
			}
		}
	} else if (mode == "New") {
		var value = data.count;
		for (var i = 0; i < tbody.rows.length; i++)
		{
			if (tbody.rows[i].className != "row")	continue;

			var v = tbody.rows[i].getAttribute("count");
			if (!v || value <= v)
			{
				place = i;
				placed = true;
				break;
			}
		}
	} else if (mode == "Level") {
		var value = data.expLevel;
		for (var i = 0; i < tbody.rows.length; i++)
		{
			if (tbody.rows[i].className != "row")	continue;

			var v = tbody.rows[i].getAttribute("level");
			if (!v || value >= v)
			{
				place = i;
				placed = true;
				break;
			}
		}
	} else {
		var value = Number(data.clanRank);
		for (var i = 0; i < tbody.rows.length; i++)
		{
			if (tbody.rows[i].className != "row")	continue;

			var v = Number(tbody.rows[i].getAttribute("rank"));
			if (!v || value <= v)
			{
				place = i;
				placed = true;
				break;
			}
		}
	}

	var tr0;
	if (placed)
	{
		tr0 = tbody.insertRow(place);
	}
	else
	{
		tr0 = document.createElement("tr");
		tbody.appendChild(tr0);
	}
	
	tr0.id = tag;
	tr0.className = "row";
	tr0.setAttribute("rank", data.clanRank);
	tr0.setAttribute("count", data.count);
	tr0.setAttribute("active", data.active);
	tr0.setAttribute("level", data.expLevel);
	var td0 = setFirstCell(data);
	tr0.appendChild(td0);

	var tr1 = document.createElement("tr");
	var tr2 = document.createElement("tr");
	var tr3 = document.createElement("tr");
	var tr4 = document.createElement("tr");
	var tr5 = document.createElement("tr");
	var tr6 = document.createElement("tr");
	var tr7 = document.createElement("tr");

	for (i=0; i<data.row0.length; i++)
	{
		tr0.appendChild(tds0[i]);
		tr1.appendChild(tds1[i]);
		tr2.appendChild(tds2[i]);
		tr3.appendChild(tds3[i]);
		tr4.appendChild(tds4[i]);
		tr5.appendChild(tds5[i]);
		tr6.appendChild(tds6[i]);
		tr7.appendChild(tds7[i]);
	}
	
	tr0.parentNode.insertBefore(tr1, tr0.nextSibling);
	tr1.parentNode.insertBefore(tr2, tr1.nextSibling);
	tr2.parentNode.insertBefore(tr3, tr2.nextSibling);
	tr3.parentNode.insertBefore(tr4, tr3.nextSibling);
	tr4.parentNode.insertBefore(tr5, tr4.nextSibling);
	tr5.parentNode.insertBefore(tr6, tr5.nextSibling);
	tr6.parentNode.insertBefore(tr7, tr6.nextSibling);

	if(--refreshedRow <= 0)
	{
		refreshedRow = 0;
		enableButtons();
	}
}

$(document).ready(function() {
	$("#default").click(function() {
		disableButtons();
		refreshRows($(this).text());
	});

	$("#active").click(function() {
		disableButtons();
		refreshRows($(this).text());
	});

	$("#inactive").click(function() {
		disableButtons();
		refreshRows($(this).text());
	});

	$("#new").click(function() {
		disableButtons();
		refreshRows($(this).text());
	});

	$("#level").click(function() {
		disableButtons();
		refreshRows($(this).text());
	});

	$.ajax({
		url: "/wsgi/set.py",
		success: function(result) {
			initSheet(result);
		}
	});
});

function disableButtons()
{
	$(".buttons button").each(function() {
		//$(this).prop('disabled', true);
		// jQuery와 JS를 섞어 써도 무방하다면 아래 코드가 좀더 명시적
		this.disabled = true;
	});
}

function enableButtons()
{
	$(".buttons button").each(function() {
		this.disabled = false;
	});
}

function refreshRows(mode = "")
{
	$("tr").each(function() {
		if(this.className == "row") {
			refreshedRow++;
			resetRow(this);

			$.ajax({
				url: "/wsgi/row.py?m=diff&t=" + this.id,
				success: function(result) {
					refreshRow(result, mode);
				}
			});
		};
	});
}
