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
		echo "<a onclick=\"toggle_visibility('detail".$author['author_id']."');\" id=\"author".$author['author_id']."\">" 
		.$tech_report->get_author_fullname($author)
		."(".$tech_report->get_author_paper_amount($author).")"
		. "</a>";
		echo "<br/><br/><p style=\"display: none ; color:black;\" id=\"detail".$author['author_id']."\">".$author['first_name']."</p>";
		echo "</td></tr>";
	endforeach;
	echo "<tbody></table>";
endforeach;
?>