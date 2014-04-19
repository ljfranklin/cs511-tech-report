<?php

$tech_report = new TechReports();
$query = "SELECT * FROM paper order by publication_year asc";
$papers=$tech_report ->paper_db->get_results($query, ARRAY_A);
$year="";

foreach ($papers as $paper):
	if($paper['publication_year']!=$year):
		$year=$paper['publication_year'];
		echo "<h1 class=\"entry-title\">".$year."</h1>";
	endif;
	$paper['file'] = $tech_report ->get_paper_url($paper);
	$authors_query = "SELECT author.* FROM paperAuthorAssoc INNER JOIN author 
			ON paperAuthorAssoc.paper_id=".$paper['paper_id']." AND paperAuthorAssoc.author_id=author.author_id";
	$paper['authors'] = $tech_report ->paper_db->get_results($authors_query, ARRAY_A);
	if (is_null($paper['authors'])):
		$paper['authors'] = array();
	endif;
		
	foreach ($paper['authors'] as $key => $author):
		$paper['authors'][$key]['full_name'] = $tech_report ->get_author_fullname($author);
	endforeach;

		echo "<header class=\"entry-header\"><div class=\"paper_display paper_expand\"><div class=\"paper_title\"><span class=\"paper_title_text\">".$paper['title']."</span><span class=\"expand_icon genericon genericon-expand\"></span><span class=\"collapse_icon genericon genericon-collapse\"></span></div><div class=\"paper_body hide\"><table class=\"paper_main_data\"><tbody><tr><th>Author:</th><td>";
		$full_names=array();
		foreach ($paper['authors'] as $author):
			array_push($full_names, $author['full_name']);
		endforeach;
		echo implode(", ", $full_names); 
		echo "</td></tr><tr><th>Publication Year:</th><td>".$paper['publication_year']."</td></tr><tr><th>Type:</th><td>".$paper['type']."</td></tr><tr><th>Download:</th><td><a href=\"".$paper['file']."\" target=\"_blank\">PDF</a></td></tr></tbody></table><div class=\"paper_citation\"><label>Citation:</label><p>";
		echo $tech_report->generate_citation($paper);
		echo "</p></div><div class=\"paper_abstract\"><label>Abstract:</label><p>";
		echo $paper['abstract'];
		echo "</p></div>";
		echo "<p class=\"read-more button\"><a href=\"";
		echo esc_url ($tech_report->get_paper_detail_url_by_paperID($paper['paper_id'])->guid);
		echo "\">";
		echo _e( 'View Details &raquo;', 'ridizain' );
		echo "</a></p>";
		echo "</div></div></header>";

endforeach;
?>
