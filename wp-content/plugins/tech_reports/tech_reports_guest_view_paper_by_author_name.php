<?php
//zongmin sun
$tech_report = new TechReports();
$initials = range('A', 'Z');
//echo "<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js\"></script>";
echo "<script>
	function toggle_visibility(id) {
       		var e = document.getElementById(id);
       		if(e.style.display == 'block') e.style.display = 'none';
       		else	e.style.display = 'block';
    	}
	</script>";
foreach ($initials as $initial):
	$authors = $tech_report->get_authors_by_initial($initial);
	if (empty($authors)) continue;
	echo "<h1>".$initial."</h1>";
	echo "<table><tbody>";
	foreach ($authors as $author):
		echo "<tr><td>";
		//$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
		echo "<a onclick=\"toggle_visibility('list".$author['author_id']."');\" id=\"author".$author['author_id']."\" style=\"cursor:pointer;\">" 
		.$tech_report->get_author_fullname($author)
		."(".$tech_report->get_author_paper_amount($author).")"
		. "</a>";

		echo "<br/><br/><div style=\"display: none ; color:black;\" id=\"list".$author['author_id']."\">";
		$author_papers=$tech_report->get_author_papers($author['author_id']);
		echo "<table><tbody>";
		foreach($author_papers as $au_pa):
			echo "<tr><td>";
			echo "<a style=\"color:#669; cursor:pointer;\" onclick=\"toggle_visibility('detail".$au_pa['paper_id']."');\" id=\"paper".$au_pa['paper_id']."\">\t" .$au_pa['title']."</a>";
			echo "<br/><p style=\"display: none ; color:black;\" id=\"detail".$au_pa['paper_id']."\">";
			echo $au_pa['title']."<br/><br/>".$au_pa['abstract'];
			echo "</p>";
		echo "</td></tr>";
		endforeach;
		echo "<tbody></table>";
		echo "</div>";

		echo "</td></tr>";
	endforeach;
	echo "<tbody></table>";
endforeach;
?>