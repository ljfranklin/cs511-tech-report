<?php
//zongmin sun
$tech_report = new TechReports();
$initials = range('A', 'Z');

foreach ($initials as $initial):
	$papers = $tech_report->get_all_papers_by_author_name($initial);
	if (empty($papers)) continue;
	echo "<h1>".$initial."</h1>";
	echo "<table><tbody>";
	foreach ($papers as $paper):
		echo "<tr><td>";
		$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
		echo "<a href=" . $url . ">" .$paper->author." - ". $paper->title . "</a>";
		echo "</td></tr>";
	endforeach;
	echo "<tbody></table>";
endforeach;
?>