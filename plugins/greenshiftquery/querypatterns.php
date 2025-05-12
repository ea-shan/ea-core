<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

//////////////////////////////////////////////////////////////////
// Query block and styles
//////////////////////////////////////////////////////////////////

function gspb_query_register_block_patterns() {

	if ( function_exists( 'register_block_pattern_category_type' ) ) {
		register_block_pattern_category_type( 'gspb_query', array( 'label' => __( 'Greenshift Query', 'gspb_query' ) ) );
	}

	$block_pattern_categories = array(
		'gspb_query-query'   => array(
			'label'         => __( 'Greenshift Query Addon', 'gspb_query' ),
			'categoryTypes' => array( 'gspb_query' ),
		),
	);

	foreach ( $block_pattern_categories as $name => $properties ) {
		register_block_pattern_category( $name, $properties );
	}

	$block_patterns = array(
		'query/query-cover',
		'query/query-cover-simple',
		'query/query-syncslider',
		'query/query-hover',
		'query/query-prevnext',
	);

	foreach ( $block_patterns as $block_pattern ) {
		register_block_pattern(
			'gspb_query/' . $block_pattern,
			require GREENSHIFTQUERY_DIR_PATH .'patterns/' . $block_pattern . '.php'
		);
	}

}

add_action( 'init', 'gspb_query_register_block_patterns', 9 );