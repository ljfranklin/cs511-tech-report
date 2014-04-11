<?php

	$tech_report = new TechReports();

	// Tech Report
	echo "<h1> Tech Report </h1>";
	
	echo "<table>";
		$items = $tech_report->get_all_papers_by_type("tech-report");
		foreach ($items as $paper):
			echo "<tr>"; echo "<td>";
				$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
				echo "<a href=" . $url . ">" . $paper->title . "</a>";
			echo "</td>"; echo "</tr>";
		endforeach;
	echo "</table>";
	
	
	
	// Journal
	echo "<h1> Journal </h1>";
	
	echo "<table>";
		$items = $tech_report->get_all_papers_by_type("journal");
		foreach ($items as $paper):
			echo "<tr>"; echo "<td>";
				$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
				echo "<a href=" . $url . ">" . $paper->title . "</a>";
			echo "</td>"; echo "</tr>";
		endforeach;
	echo "</table>";
	
	
	// Conference
	echo "<h1> Conference </h1>";
	
	echo "<table>";
		$items = $tech_report->get_all_papers_by_type("conference");
		foreach ($items as $paper):
			echo "<tr>"; echo "<td>";
				$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
				echo "<a href=" . $url . ">" . $paper->title . "</a>";
			echo "</td>"; echo "</tr>";
		endforeach;
	echo "</table>";
	
	
	// PhD Dissertation
	echo "<h1> PhD Dissertation </h1>";
	
	echo "<table>";
		$items = $tech_report->get_all_papers_by_type("phd");
		foreach ($items as $paper):
			echo "<tr>"; echo "<td>";
				$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
				echo "<a href=" . $url . ">" . $paper->title . "</a>";
			echo "</td>"; echo "</tr>";
		endforeach;
	echo "</table>";

?>
