<?php
$tech_report = new TechReports();
$years = $tech_report->get_all_years();

foreach ($years as $row):
	echo "<h1>".$row->publication_year."</h1>";
	echo "<table>";
	//echo "<tr><td>";
	
		//echo "<table><tbody>";
		$items = $tech_report->get_all_papers_by_year($row->publication_year);
		foreach ($items as $paper):
			echo "<tr><td>";
				$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
				echo "<a href=" . $url . ">" . $paper->title . "</a>";
			echo "</td></tr>";
		endforeach;
		//echo "<tbody></table>";
	//echo "</td></tr>";
	echo "</table>";

endforeach;


?>
