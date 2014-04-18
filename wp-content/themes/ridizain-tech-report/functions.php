<?php

	add_action( 'wp_enqueue_scripts', 'load_tech_report_scripts' );
	function load_tech_report_scripts() {
		wp_enqueue_script(
			'tech_reports',
			get_stylesheet_directory_uri() . '/scripts/tech_reports.js',
			array( 'jquery' )
		);
	}
?>
