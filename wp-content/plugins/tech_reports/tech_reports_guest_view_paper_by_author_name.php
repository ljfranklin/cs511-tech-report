<?php

$tech_report = new TechReports();

$get_keyword_links = function($paper) {
	$keywords = explode(',', $paper['keywords']);
	
	$to_link = function($keyword) {
		return '<a href="' . site_url() . '/?s=' . $keyword . '">' . $keyword . '</a>';
	};
	
	return implode(', ', array_map($to_link, $keywords));
};

//$record_papers=array();
$record_authors=array();
$query = "SELECT * FROM author order by last_name";
$authors=$tech_report ->paper_db->get_results($query, ARRAY_A);
foreach($authors as $author):
	$record_authors[$author['author_id']]=array();
	$query="select paper.* from paper inner join paperAuthorAssoc on paper.paper_id=paperAuthorAssoc.paper_id inner join author on paperAuthorAssoc.author_id=".$author['author_id'];
	$papers=$tech_report ->paper_db->get_results($query, ARRAY_A);
	foreach($papers as $paper):
		//if(!array_key_exists ($paper['paper_id'],$record_papers)):
		//	$record_papers[$paper['paper_id']]=array();
		//endif;
		//if(!array_key_exists ($author['author_id'],$record_papers[$paper['paper_id']])):
		//	$record_papers[$paper['paper_id']][$author['author_id']]=$author;
		//endif;
		if(!array_key_exists ($paper['paper_id'],$record_authors[$author['author_id']])):
			$paper['identifier'] = $tech_report->get_paper_identifier($paper['paper_id'], $paper['publication_year']);
			$paper['keywords'] = $get_keyword_links($paper);
			$record_authors[$author['author_id']][$paper['paper_id']]=$paper;
		endif;
	endforeach;
endforeach;
$initial="";

foreach ($authors as $author):
	if(strtoupper($author['last_name'][0])!=$initial):
		$initial=strtoupper($author['last_name'][0]);
		echo "<h1 class=\"entry-title\">".$initial."</h1>";
	endif;

		echo "<header class=\"entry-header\"><div class=\"paper_display paper_expand\"><div class=\"paper_title\"><span class=\"paper_title_text\">".$author['first_name']." ".$author['middle_name']." ".$author['last_name']."</span><span class=\"expand_icon genericon genericon-expand\"></span><span class=\"collapse_icon genericon genericon-collapse\"></span></div><div class=\"paper_body hide\">";
		echo "<table class=\"paper_main_data\"><tbody>";
		foreach($record_authors[$author['author_id']] as $paper):
			echo "<tr><td><div class=\"paper_display paper_expand\"><div class=\"paper_title\"><span class=\"paper_title_text\">";
			echo $paper['title'];
			echo "</span><span> - </span>";
			echo "<span>" . $paper['identifier'] . '</span>';
			echo "<span class=\"expand_icon genericon genericon-expand\"></span><span class=\"collapse_icon genericon genericon-collapse\"></span></div><div class=\"paper_body hide\">";

			echo "<table class=\"paper_main_data\"><tbody><tr><th>Author:</th><td>";
			//foreach ($record_papers[$paper['paper_id']] as $author2):
			//	echo $author2['first_name']." ".$author2['middle_name']." ".$author2['last_name']." , ";
			//endforeach;

			$paper['file'] = $tech_report ->get_paper_url($paper);
			$authors_query = "SELECT author.* FROM paperAuthorAssoc INNER JOIN author ON paperAuthorAssoc.paper_id=".$paper['paper_id']." AND paperAuthorAssoc.author_id=author.author_id";
			$paper['authors'] = $tech_report ->paper_db->get_results($authors_query, ARRAY_A);
			if (is_null($paper['authors'])):
				$paper['authors'] = array();
			endif;
			
			$full_names=array();
			foreach ($paper['authors'] as $key => $author):
				$paper['authors'][$key]['full_name'] = $tech_report ->get_author_fullname($author);
			endforeach;
			foreach ($paper['authors'] as $author):
				array_push($full_names, $author['full_name']);
			endforeach;
			echo implode(", ", $full_names); 

			echo "</td></tr><tr><th>Publication Year:</th><td>".$paper['publication_year']."</td></tr><tr><th>Type:</th><td>".$paper['type']."</td></tr><tr><th>Download:</th><td><a href=\"".$paper['file']."\" target=\"_blank\">PDF</a></td></tr><tr><th>Keywords:</th><td>".$paper['keywords']."</td></tr></tbody></table><div class=\"paper_citation\"><label>Citation:</label><p>";
			echo $tech_report->generate_citation($paper);
			echo "</p></div><div class=\"paper_abstract\"><label>Abstract:</label><p>";
			echo $paper['abstract'];
			echo "</p></div>";
			echo "<p class=\"read-more button\"><a href=\"";
			echo esc_url ($tech_report->get_paper_detail_url_by_paperID($paper['paper_id'])->guid);
			echo "\">";
			echo _e( 'View Details &raquo;', 'ridizain' );
			echo "</a></p>";

			echo "</div></td></tr>";
		endforeach;
		echo "</tbody></table>";
		echo "</div>";
		echo "</header>";

endforeach;
?>
