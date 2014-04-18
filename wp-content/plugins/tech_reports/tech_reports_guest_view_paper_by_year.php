<?php
$tech_report = new TechReports();
$years = $tech_report->get_all_years();

echo "<script>
	function toggle_visibility(id) {
       		var e = document.getElementById(id);
       		if(e.style.display == 'block') e.style.display = 'none';
       		else	e.style.display = 'block';
    	}
	</script>";

foreach ($years as $row):
	echo "<h1>".$row->publication_year."</h1>";
	echo "<table>";
	//echo "<tr><td>";
	
		//echo "<table><tbody>";
		$items = $tech_report->get_all_papers_by_year($row->publication_year);
		foreach ($items as $paper):
			echo "<tr><td>";
				$url = esc_url ($tech_report->get_paper_detail_url_by_paperID($paper->paper_id)->guid);
				echo "<a  style=\"color:#669;cursor:pointer;\" onclick=\"toggle_visibility('detail".$paper->paper_id."');\" id=\"paper".$paper->paper_id."\">";
				echo $paper->title . "</a>";
				echo "<br/><br/><p style=\"display: none ; color:black;\" id=\"detail".$paper->paper_id."\">";
				echo $paper->title."<br/><br/>".$paper->abstract."</p>";
			echo "</td></tr>";
		endforeach;
		//echo "<tbody></table>";
	//echo "</td></tr>";
	echo "</table>";

endforeach;


?>