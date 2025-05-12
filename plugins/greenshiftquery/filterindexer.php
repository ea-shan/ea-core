<?php 

add_action('admin_menu', 'gspb_filter_admin_opts', 200);
/**
 * Registers the custom admin menu item for the GSPB Filter plugin.
 */
function gspb_filter_admin_opts() {
	add_submenu_page(
		'greenshift_dashboard',
		esc_html__('GSPB Filter Block Settings', 'greenshiftquery'),
		esc_html__('Filter Indexer', 'greenshiftquery'),
		'manage_options',
		'greenshift_filter_settings',
		'gspb_filter_settings_page_callback',
		200
	);
}

/**
 * Callback function to render the settings page for the GSPB Filter plugin.
 */
function gspb_filter_settings_page_callback() {
    $post_types = get_post_types();
    ?>
    <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); max-width: 400px; margin: 40px auto 0px;">
        <h3 style="font-size: 24px; color: #333; margin-bottom: 20px;text-align: center;">Indexing</h3>
        <form id="gspb-indexing-form">
            <div style="margin-bottom: 12px">
                <label for="post-type-select" style="font-weight: bold; display: block; margin-bottom: 8px;">Select Post Type</label>
                <select id="post-type-select" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option><?php echo esc_html__('Select Post Type', 'greenshiftquery'); ?></option>
                    <?php 
                        foreach ( $post_types as $key => $post_type ) {                     
                            ?>
                            <option value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
        <button type="submit" id="submit-indexing" style="width: 100%; padding: 10px; background-color: #0073aa; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Submit</button>
        <p class="response-div" style="display: none;padding: 8px;margin-top: 20px;border-radius: 8px;background-color: #d4edda;color: #155724;border: 1px solid #c3e6cb;font-size: 16px;text-align: center;box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    ">Your success message here.</p>
    </form>
    </div>
    <script>
        const rest_url = window.location.origin + "/wp-json/greenshift/v1/gspb-indexing";
        var currentPage = 1;
        const postsPerPage = 50;
        const delayBetweenRequests = 2000; 

        jQuery(document).ready(function(){
           jQuery('#gspb-indexing-form').on('submit', function(event) {
                event.preventDefault(); 

                jQuery('#submit-indexing').prop('disabled', true);
                jQuery('#submit-indexing').text("In Progress...Do not close the page");
                jQuery('#submit-indexing').css('opacity', '0.5');

                let postType =jQuery('#post-type-select').val();

                makeIndexingRequest(postType, currentPage); 
            });
        });

        // Rest Call for indexing
        function makeIndexingRequest( postType, page ) {
            const request = new XMLHttpRequest();
            request.open("POST", rest_url, true);
            request.setRequestHeader(
                "Content-Type",
                "application/x-www-form-urlencoded; charset=UTF-8"
            );
            request.responseType = "json";

            request.onload = function () {
                if (this.status >= 200 && this.status < 400) {
                    let responseObj = this.response;
                    if (responseObj.data.morePages) {
                        setTimeout(function() {
                            makeIndexingRequest(postType, currentPage);
							jQuery('#submit-indexing').text("Indexing page " + currentPage + "...");
                        }, delayBetweenRequests);
                        
                        currentPage++;
                    } else {
                        // No more pages available, reset UI state if needed
                        jQuery('#submit-indexing').prop('disabled', false);
                        jQuery('#submit-indexing').text("Submit");
                        jQuery('#submit-indexing').css('opacity', '1');
                        jQuery(".response-div").show();
                        jQuery(".response-div").text("Indexing completed!");
                    }
                } else {
                    console.error("Request failed");
                }
            };

            request.onerror = function () {
                console.error("Request failed");
            };

            request.send(
                "action=gspb_indexer" +
                "&postType=" + postType +
                "&currentpage=" + page +
                "&limit=" + postsPerPage 
            );
        }

    </script>

    <?php
}

/**
 * Handles filtering and pagination for posts based on REST API request parameters.
 *
 * This function processes the incoming REST API request to filter and paginate posts,
 * then returns the HTML output and pagination controls in the response.
 *
 * @param WP_REST_Request $request The REST API request object.
 */
function gspb_filter_posts( WP_REST_Request $request ) {
	$get_params = $request->get_params();
	$response_arr = array();

	$paginationhtml = '';

	$args = isset( $get_params['filterargs'] ) && ! empty( $get_params['filterargs'] )  ? json_decode( $get_params['filterargs'], true ) : array();
	$template = isset( $get_params['template'] ) ? $get_params['template'] : '';
	$containerid = isset( $get_params['containerid'] ) ? $get_params['containerid'] : '' ;
	$offset = ( ! empty( $get_params['offset'] ) ) ? intval( $get_params['offset'] ) : 0;
	$innerargs = isset( $get_params['innerargs'] ) && ! empty( $get_params['innerargs'] )  ? json_decode( $get_params['innerargs'], true ) : array();
	$blockinstance = ( ! empty( $_POST['blockinstance'] ) ) ? json_decode( stripslashes( $_POST['blockinstance'] ), true ) : array(); 
 	$querycontainer = isset( $get_params['querycontainer'] ) ? $get_params['querycontainer'] : '';

	$filterconnectionid = isset( $get_params['filterconnection'] ) ? $get_params['filterconnection'] : '' ;
	$current_page = ( isset( $get_params['currentpage'] ) ) ? intval( $get_params['currentpage'] ) : 1;

	$paginationtype = isset( $get_params['paginationtype'] ) ? $get_params['paginationtype'] : '';
	$loadmoretext = isset( $get_params['loadmoretext'] ) ? $get_params['loadmoretext'] : esc_html__('Load More', 'greenshiftquery');

	$response = '';
	$page_sorting = '';
	$chips_html = '';

	$offsetnext = ( ! empty( $args['posts_per_page'] ) ) ? (int) $offset + $args['posts_per_page'] : (int) $offset + 12;
	$perpage = ( ! empty( $args['posts_per_page'] ) ) ? $args['posts_per_page'] : 12;

	if ( '' != $offset ) {
		$args['offset'] = $offset;
	}

	if ( $current_page > 1) {
		$args['offset'] = ($current_page - 1) * $perpage;
	}

	$allArgs = isset( $get_params['allArgs'] ) ? json_decode( $get_params['allArgs'], true ) : array();
	$filteredIndexData = ! empty( $allArgs['filteredIndexData'] ) ? $allArgs['filteredIndexData'] : array();

	$args['post_status'] = 'publish';

	// New Code
	$i = 1 + $offset; 
	$count = 1;

	// New Code
	if ( 'product' === $args['post_type'] && isset($args['meta_key']) && ( '_wc_average_rating' === $args['meta_key'] || 'total_sales' === $args['meta_key']) ) {

		$product_query = array(
            'post_type'      => 'product',
            'posts_per_page' => $perpage,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => array('publish', 'acf-disabled'),
            'tax_query'      => isset($args['tax_query']) ? $args['tax_query'] : array(),
            'meta_query'     => isset($args['meta_query']) ? $args['meta_query'] : array(),
			'offset'         => $args['offset'],
			'post_status'    => 'publish',
        );

		if( '_wc_average_rating' === $args['meta_key'] ){
			// Custom SQL modifications through 'posts_clauses' filter
			add_filter('posts_clauses', 'gspb_sorting_product_average_rating_clauses', 10, 2);
			$wp_query = new \WP_Query( $product_query );
			remove_filter('posts_clauses', 'gspb_sorting_product_average_rating_clauses'); // Remove filter after query
		} else if( 'total_sales' === $args['meta_key'] ){
			// Custom SQL modifications through 'posts_clauses' filter
			add_filter('posts_clauses', 'gspb_sorting_product_total_sales_clauses', 10, 2);
			$wp_query = new \WP_Query( $product_query );
			remove_filter('posts_clauses', 'gspb_sorting_product_total_sales_clauses'); // Remove filter after query
		}

	} else {
		$wp_query = new \WP_Query($args);
	}

	$pages = $wp_query->max_num_pages;

	$prev_page = max(1, $current_page - 1); 
	$next_page = $current_page + 1; 

	$inlinestyle = ! empty( $blockinstance ) ? gsSearchInlineCssStyles( $blockinstance ) : '';

	$has_filters = false;

	// Check if tax_query has any terms
	if ( ! empty( $filteredIndexData['tax_query'] ) ) {
		foreach ( $filteredIndexData['tax_query'] as $taxonomy => $terms ) {
			if ( ! empty($terms) ) {
				$has_filters = true;
				break;
			}
		}
	}

	// Check if meta_query has any values
	if ( ! $has_filters ) {
		foreach ( $filteredIndexData['meta_query'] as $meta_key => $meta_values ) {
			if ( strpos( $meta_key, '|range' ) !== false ) {
				if ( ! empty( $meta_values ) && isset( $meta_values['minmax'] ) ) {
					$minMax_explode = explode( '|', $meta_values['minmax'] );
					$key_base = str_replace( '|range', '', $meta_key );

					if( $minMax_explode[0] !== $meta_values['defaultMin'] || $minMax_explode[1] !== $meta_values['defaultMax'] ) {
						$has_filters = true;
						break;
					} 
				}
			} 
			
			if ( strpos( $meta_key, '|range' ) === false && ! empty( $values ) ) {
				$has_filters = true;
				break;
			}
		}
	}

	// Check if search_keyword is not empty
	if ( ! $has_filters && ! empty( $filteredIndexData['search_keyword'] ) ) {
		$has_filters = true;
	}

	if ( $wp_query->have_posts() ) {
		ob_start();
		if($inlinestyle){
			echo '<style scoped>'.$inlinestyle.'</style>';
		}
		
		$chips_html .= '<ul class="gspb-filter-chips-list">';

			if ( $has_filters ) {
				$chips_html .= '<li class="gspb-filter-chip gspb-chip-reset-all">';
					$chips_html .= '<div class="gspb-chip-content">';
					$chips_html .= '<span class="gspb-filter-chip-name">Reset All</span>';
					$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
					$chips_html .= '</div>';
				$chips_html .= '</li>';
			}

		if( isset( $filteredIndexData['tax_query'] ) ) {
			foreach ( $filteredIndexData['tax_query'] as $taxonomy => $terms ) {
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term_id ) {
						if( ! empty( $term_id ) ) {
							$term = get_term_by( 'id', $term_id, $taxonomy ); 
							$chips_html .= '<li class="gspb-filter-chip" data-key="' . $taxonomy . '" data-slug="' . $term->slug . '" data-val="' . $term->term_id . '" data-filter="tax_query">';
								$chips_html .= '<div class="gspb-chip-content">';
								$chips_html .= '<span class="gspb-filter-chip-name">'. $term->name .'</span>';
								$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
								$chips_html .= '</div>';
							$chips_html .= '</li>';
						}
					}
				}
			}
		}

		if ( isset( $filteredIndexData['meta_query'] ) ) {
			// Loop through meta_query to find the key with '|range'
			foreach  ( $filteredIndexData['meta_query'] as $key => $meta_values  ) {
				if ( strpos( $key, '|range' ) !== false ) {

					if ( ! empty( $meta_values ) && isset( $meta_values['minmax'] ) ) {
						$minMax_explode = explode( '|', $meta_values['minmax'] );
						$key_base = str_replace( '|range', '', $key );

						$dataMin = $minMax_explode[0] !== $meta_values['defaultMin'] ? $minMax_explode[0] : $meta_values['defaultMin'];
						$dataMax = $minMax_explode[1] !== $meta_values['defaultMax'] ? $minMax_explode[1] : $meta_values['defaultMax'];
					
						// Check and generate HTML for minimum value if it exceeds the default minimum
						if ( $minMax_explode[0] !== $meta_values['defaultMin'] ) {
							$chips_html .= '<li data-class="' . $key_base . '_min" class="gspb-filter-chip" data-key="' . $key_base . '" data-slug="' . esc_html($key_base) . '" data-val="range__input" data-max="' . $dataMax . '" data-min="' . $meta_values['defaultMin'] . '" data-val="range" data-filter="meta_query" data-active="minActive">';
							$chips_html .= '<div class="gspb-chip-content">';
							$chips_html .= '<span class="gspb-filter-chip-name">Min $' . $minMax_explode[0] . '</span>';
							$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
							$chips_html .= '</div>';
							$chips_html .= '</li>';
						}

						// Check and generate HTML for maximum value if it is less than the default maximum
						if ( $minMax_explode[1] !== $meta_values['defaultMax'] ) {
							$chips_html .= '<li data-class="' . $key_base . '_max" class="gspb-filter-chip" data-key="' . $key_base . '" data-slug="' . esc_html($key_base) . '" data-val="range__input" data-max="' . $meta_values['defaultMax'] . '" data-min="' . $dataMin . '" data-val="range" data-filter="meta_query" data-active="maxActive">';
							$chips_html .= '<div class="gspb-chip-content">';
							$chips_html .= '<span class="gspb-filter-chip-name">Max $' . $minMax_explode[1] . '</span>';
							$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
							$chips_html .= '</div>';
							$chips_html .= '</li>';
						}

					}
				}
			}

			foreach ( $filteredIndexData['meta_query'] as $key => $values ) {
				// Check if it's not the range key and if it's not empty
				if ( strpos( $key, '|range' ) === false && ! empty( $values ) ) {

					foreach ( $values as $value ) {
						$chips_html .= '<li class="gspb-filter-chip" data-key="' . esc_html($key) . '" data-slug="' . esc_html($value) . '" data-val="' . esc_html($value) . '" data-filter="meta_query">';
						$chips_html .= '<div class="gspb-chip-content">';
						$chips_html .= '<span class="gspb-filter-chip-name">' . esc_html($value) . '</span>';
						$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
						$chips_html .= '</div>';
						$chips_html .= '</li>';
					}
				}
			}

		}

		if ( isset($filteredIndexData['search_keyword']) && $filteredIndexData['search_keyword'] !== "" ) {
			$chips_html .= '<li class="gspb-filter-chip" data-slug="' .  esc_html( $filteredIndexData['search_keyword'] ) . '" data-val="gspb-search">';
			$chips_html .= '<div class="gspb-chip-content">';
			$chips_html .= '<span class="gspb-filter-chip-name">search: ' . esc_html( $filteredIndexData['search_keyword'] ) . '</span>';
			$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
			$chips_html .= '</div>';
			$chips_html .= '</li>';
		}

		$chips_html .= '</ul>';

		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();
			if ( ! empty( $innerargs )) {
				extract($innerargs);
			}

			include(GREENSHIFTQUERY_DIR_PATH . 'parts/' . $template . '.php');
			$count++;
			$i++;
		}
		wp_reset_query();
		$response .= ob_get_clean();

		switch ($paginationtype) {
			case 'numericpagi':
			if( $pages > 1 ){
				$paginationhtml .= '<ul class="gspb-filter__ajx-pages">';	
				$paginationhtml .= '<li class="gspb-filter__ajx-page prev-' . $filterconnectionid . '" data-connection="' . $filterconnectionid . '" data-key="gspbpagination" data-page="' . $prev_page . '" data-type="prev" style="display:none" data-paginationtype="numericpagi">Prev</li>';
					for ($i=1; $i <= $pages; $i++) { 
						$paginationhtml .= '<li class="gspb-filter__ajx-page" data-connection="' . $filterconnectionid . '" data-key="gspbpagination" data-page="' . $i . '" data-paginationtype="numericpagi">'. $i .'</li>';
					}					
				if ($current_page < $pages) {
					$paginationhtml .= '<li class="gspb-filter__ajx-page next-' . $filterconnectionid . '" data-connection="' . $filterconnectionid . '" data-key="gspbpagination" data-page="' . $next_page . '" data-type="next" data-paginationtype="numericpagi">Next</li>';
				}
				$paginationhtml .= '</ul>';
			}
			break;
			case 'loadmore':
				if( $pages > 1 ){
					$paginationhtml .= '<div class="gspb-filter__ajx-inner">';
					$paginationhtml .= '<span data-offset="" data-connection="' . $filterconnectionid . '" class="gspb-filter__load_moreajx_btn" id="'. $filterconnectionid .'-ajxloadmore" data-type="loadmorepagi" data-key="gspbpagination" data-paginationtype="loadmore">' . $loadmoretext . '</span>';
					$paginationhtml .= '</div>';
				}
			break;
			case 'infinitescroll':
				if( $pages > 1 ){
					$paginationhtml .= '<div class="gspb-filter__ajx-inner">';
					$paginationhtml .= '<span data-offset="" data-connection="' . $filterconnectionid . '" class="gspb-filter__load_moreajx_btn" id="'. $filterconnectionid .'-ajxloadmore" data-type="loadmorepagi" data-key="gspbpagination" data-paginationtype="infinitescroll"></span>';
					$paginationhtml .= '</div>';
				}
			break;
			default:
			break;
		}

	} else {
		$response .= '<div class="clearfix flexbasisclear gcnomoreclass"><span class="no_more_posts"><span></div>';
	}
	
	$response_arr = array(
		"html"           => $response,
		"paginationHtml" => $paginationhtml,
		"currentPage"   => $current_page,
		"args"			=> $args,
		"maxPage"       => $pages,
		"filterConnectionId" => "currentpage-".$filterconnectionid,
		// "chipsHtml"		 => $chips_html,
		// New Code
		"hasFilters"     => $has_filters,
		"resultsCount"   => array(
			'totalPosts'     => $wp_query->found_posts,
			'postsOnPage'    => $wp_query->post_count,
			'postsPerPage'   => $perpage,
			'startCount'     => ( ( $current_page - 1 ) * $perpage ) + 1,
			'endCount'       => min( ( $current_page * $perpage ), $wp_query->found_posts )
		)
	);

	wp_send_json_success( $response_arr );
	exit();
}

/**
 * Handles filtering chips.
 *
 * @param WP_REST_Request $request The REST API request object.
 */
function gspb_refreshfilter_chips_callback( WP_REST_REQUEST $request ) {
	$get_params = $request->get_params();
	$response_arr = array();
	$filteredIndexData = isset( $get_params['filteredIndexData'] ) ? $get_params['filteredIndexData'] : array();
	$dataConnectionId = isset( $get_params['dataConnectionId'] ) ? $get_params['dataConnectionId'] : array();

	$has_filters = false;
	$chips_html = '';

	// Check if tax_query has any terms
	if ( ! empty( $filteredIndexData['tax_query'] ) ) {
		foreach ( $filteredIndexData['tax_query'] as $taxonomy => $terms ) {
			if ( ! empty($terms) ) {
				$has_filters = true;
				break;
			}
		}
	}

	// Check if meta_query has any values
	if ( ! $has_filters ) {
		foreach ( $filteredIndexData['meta_query'] as $meta_key => $meta_values ) {
			if ( strpos( $meta_key, '|range' ) !== false ) {
				if ( ! empty( $meta_values ) && isset( $meta_values['minmax'] ) ) {
					$minMax_explode = explode( '|', $meta_values['minmax'] );
					$key_base = str_replace( '|range', '', $meta_key );

					if( $minMax_explode[0] !== $meta_values['defaultMin'] || $minMax_explode[1] !== $meta_values['defaultMax'] ) {
						$has_filters = true;
						break;
					}
				}
			} 

			if ( strpos( $meta_key, '|range' ) === false && ! empty( $meta_values ) ) {
				$has_filters = true;
				break;
			}

		}
	}

	// Check if search_keyword is not empty
	if ( ! $has_filters && ! empty( $filteredIndexData['search_keyword'] ) ) {
		$has_filters = true;
	}

	$chips_html .= '<ul class="gspb-filter-chips-list">';

	if ( $has_filters ) {
		$chips_html .= '<li class="gspb-filter-chip gspb-chip-reset-all" data-connectionid="' . $dataConnectionId . '">';
			$chips_html .= '<div class="gspb-chip-content">';
			$chips_html .= '<span class="gspb-filter-chip-name">Reset All</span>';
			$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
			$chips_html .= '</div>';
		$chips_html .= '</li>';
	}

	if( isset( $filteredIndexData['tax_query'] ) ) {
		foreach ( $filteredIndexData['tax_query'] as $taxonomy => $terms ) {
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term_id ) {
					if( ! empty( $term_id ) ) {
						$term = get_term_by( 'id', $term_id, $taxonomy ); 
						$chips_html .= '<li class="gspb-filter-chip" data-key="' . $taxonomy . '" data-slug="' . $term->slug . '" data-val="' . $term->term_id . '" data-filter="tax_query" data-connectionid="' . $dataConnectionId . '">';
							$chips_html .= '<div class="gspb-chip-content">';
							$chips_html .= '<span class="gspb-filter-chip-name">'. $term->name .'</span>';
							$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
							$chips_html .= '</div>';
						$chips_html .= '</li>';
					}
				}
			}
		}
	}

	if ( isset( $filteredIndexData['meta_query'] ) ) {
		// Loop through meta_query to find the key with '|range'
		foreach  ( $filteredIndexData['meta_query'] as $key => $meta_values  ) {
			if ( strpos( $key, '|range' ) !== false ) {

				if ( ! empty( $meta_values ) && isset( $meta_values['minmax'] ) ) {
					$minMax_explode = explode( '|', $meta_values['minmax'] );
					$key_base = str_replace( '|range', '', $key );

					$dataMin = $minMax_explode[0] !== $meta_values['defaultMin'] ? $minMax_explode[0] : $meta_values['defaultMin'];
					$dataMax = $minMax_explode[1] !== $meta_values['defaultMax'] ? $minMax_explode[1] : $meta_values['defaultMax'];
					
					$dataMinMax = $meta_values['defaultMin'] . '|' . $meta_values['defaultMax'];

					// Check and generate HTML for minimum value if it exceeds the default minimum
					if ( $minMax_explode[0] !== $meta_values['defaultMin'] ) {
						$chips_html .= '<li data-minmax="' . $dataMinMax . '" data-class="' . $key_base . '_min" class="gspb-filter-chip" data-key="' . $key_base . '" data-slug="' . esc_html($key_base) . '" data-type="range__input" data-max="' . $dataMax . '" data-min="' . $meta_values['defaultMin'] . '" data-filter="meta_query" data-connectionid="' . $dataConnectionId . '">';
						$chips_html .= '<div class="gspb-chip-content">';
						$chips_html .= '<span class="gspb-filter-chip-name">Min $' . $minMax_explode[0] . '</span>';
						$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
						$chips_html .= '</div>';
						$chips_html .= '</li>';
					}

					// Check and generate HTML for maximum value if it is less than the default maximum
					if ( $minMax_explode[1] !== $meta_values['defaultMax'] ) {
						$chips_html .= '<li data-minmax="' . $dataMinMax . '" data-class="' . $key_base . '_max" class="gspb-filter-chip" data-key="' . $key_base . '" data-slug="' . esc_html($key_base) . '" data-type="range__input" data-max="' . $meta_values['defaultMax'] . '" data-min="' . $dataMin . '" data-filter="meta_query" data-connectionid="' . $dataConnectionId . '">';
						$chips_html .= '<div class="gspb-chip-content">';
						$chips_html .= '<span class="gspb-filter-chip-name">Max $' . $minMax_explode[1] . '</span>';
						$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
						$chips_html .= '</div>';
						$chips_html .= '</li>';
					}

				}
			}
		}

		foreach ( $filteredIndexData['meta_query'] as $key => $values ) {
			// Check if it's not the range key and if it's not empty
			if ( strpos( $key, '|range' ) === false && ! empty( $values ) ) {

				foreach ( $values as $value ) {
					// New Code
					if ( $key === "_wc_average_rating" ) {
						$value_text = (int) $value . '&nbsp; Rated';
					} else {
						$value_text = $value;	
					}

					$chips_html .= '<li class="gspb-filter-chip" data-key="' . esc_html($key) . '" data-slug="' . esc_html($value) . '" data-val="' . esc_html($value) . '" data-filter="meta_query" data-connectionid="' . $dataConnectionId . '">';
					$chips_html .= '<div class="gspb-chip-content">';
					$chips_html .= '<span class="gspb-filter-chip-name">' . esc_html( $value_text ) . '</span>';	// New Code
					$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
					$chips_html .= '</div>';
					$chips_html .= '</li>';
				}
			}
		}

	}

	if ( isset($filteredIndexData['search_keyword']) && $filteredIndexData['search_keyword'] !== "" ) {
		$chips_html .= '<li class="gspb-filter-chip" data-slug="' .  esc_html( $filteredIndexData['search_keyword'] ) . '" data-val="gspb-search" data-connectionid="' . $dataConnectionId . '">';
		$chips_html .= '<div class="gspb-chip-content">';
		$chips_html .= '<span class="gspb-filter-chip-name">search: ' . esc_html( $filteredIndexData['search_keyword'] ) . '</span>';
		$chips_html .= '<span class="gspb-chip-remove-icon">x</span>';
		$chips_html .= '</div>';
		$chips_html .= '</li>';
	}

	$chips_html .= '</ul>';

	$response_arr = array(
		"chipsHtml"		 => $chips_html,
		'hasFilters'     => $has_filters
	);

	wp_send_json_success( $response_arr );
	exit();
}

/**
 * Handles the REST API request to count indexed items based on provided filters.
 *
 * @param WP_REST_Request $request The request object containing parameters for filtering.
 */
function gspb_indexer_count_callback( WP_REST_Request $request ) {
	$params = $request->get_params();
	$response = array();

	$indexing_data = isset( $params['filterd_data'] ) ? $params['filterd_data'] : array();
	$filterd_tax_query = isset( $indexing_data['tax_query'] ) ? $indexing_data['tax_query'] : array();
	$filterd_meta_query = isset( $indexing_data['meta_query'] ) ? $indexing_data['meta_query'] : array();
	$post_type = isset( $indexing_data['post_type'] ) ? $indexing_data['post_type'] : false;
	$search_key = isset( $indexing_data['search_keyword'] ) ? $indexing_data['search_keyword'] : false;
	$tax_operator = isset( $indexing_data['taxOperator'] ) ? $indexing_data['taxOperator'] : "IN";
	$data_limit = isset( $params['data_limit'] ) ? $params['data_limit'] : -1;	// New Code
	$filter_enable_pagination = isset( $params['filter_enable_pagination'] ) ? $params['filter_enable_pagination'] : false;
	$exclude_taxonomy = isset( $params['exclude_taxonomy'] ) ? $params['exclude_taxonomy'] : array();
	$include_taxonomy = isset( $params['include_taxonomy'] ) ? $params['include_taxonomy'] : array();

	if ( ! $post_type ) {
		$response['success'] = false;
		$response['message'] = __( 'Please provide post type.', 'greenshiftquery' );

		return wp_send_json( $response );
		wp_die();

	} 

	// New Code
	$args = array(
		'post_type' => $post_type,
		'posts_per_page' => $data_limit
	);

	$products_query = get_posts($args);
	$product_count = count($products_query);
	$total_count = wp_count_posts($post_type)->publish;

	if ( gspb_is_effectively_empty( $filterd_tax_query ) && gspb_is_effectively_empty( $filterd_meta_query ) && "" === $search_key ){

		$get_all_count = gspb_get_filtered_counts( $post_type, $filterd_tax_query, $filterd_meta_query, $exclude_taxonomy, $include_taxonomy );

		// New Code
		$response['success']  = true;
		$response['indexing'] = $get_all_count;
		$response['results']  = array(
			'total_count'   => $total_count,
			'queried_count' => $filter_enable_pagination ? "1-".$product_count : $product_count,
		);

		return wp_send_json( $response );
		wp_die();
	}

	global $wpdb;
	$table_name = $wpdb->prefix.'gspb_filters_indexer';

	$queried_ids = gspb_get_queried_ids( $post_type, $filterd_tax_query, $filterd_meta_query, $search_key, $tax_operator, $exclude_taxonomy, $include_taxonomy );

	if( empty( $queried_ids ) ) {
		$results_arr = array(
			'tax_query' => array(),
			'meta_query' => array()
		);
	
		$response['success'] = true;
		$response['indexing'] = $results_arr;
	
		return wp_send_json( $response );
		wp_die();
	}

	$indexing_all_data = gspb_get_unique_terms_and_meta_values( $post_type, $filterd_tax_query, $filterd_meta_query );

	$has_meta_query = true;
	$sql_and = '';

	foreach ( $indexing_all_data as $query_type => $query_data ) {
		switch ( $query_type ) {
			case 'tax_query':
		
				foreach ( $query_data as $tax_key => $tax_data ) {			
					$sql_and .= $sql_and ? ' OR ' : '';
					$sql_and .= "(item_query = '$query_type' AND item_key = '$tax_key' AND item_value IN ('" . implode( "','", $tax_data ) . "'))";
				}
				break;
			case 'meta_query':
				
				foreach ( $query_data as $meta_key => $meta_data ) {
					$item_key_condition = strpos($meta_key, ',')
					? "item_key IN ('" . str_replace([",", ' '], ["','", ''], $meta_key) . "')"
					: "item_key = '$meta_key'";
				
					if ($meta_data) {
						foreach ($meta_data as &$value) {
							$value = addslashes($value);
						}
						
						if ($sql_and) {
							$sql_and .= ' OR ';
						}
						$sql_and .= "(item_query = '$query_type' AND $item_key_condition AND item_value IN ('" . implode("','", $meta_data) . "'))";
					}
				}
				break;
			default:
				break;
		}	
	}

	if ( $sql_and ) {
		$sql_and = "AND ($sql_and)";
	}

	$sql = "
        SELECT MAX(item_query) as item_query, MAX(item_key) as item_key, item_value, COUNT(item_id) as count
        FROM $table_name
        WHERE item_id IN (" . implode( ",", $queried_ids ) . ")
        AND (type = '$post_type')
        $sql_and
        GROUP BY item_key, item_value
        ORDER BY item_value ASC";

	$results = $wpdb->get_results( $sql, ARRAY_A );

	$tax_query = array();
	$meta_query = array();
	$results_arr = array();

	foreach ( $results as $item ) {
		$query_type = $item['item_query'];
		$key = $item['item_key'];
		$item_value = $item['item_value'];
		$item_count = (int) $item['count'];
	
		if ($query_type === 'tax_query') {
			if ( ! isset( $tax_query[$key] ) ) {
				$tax_query[$key] = array();
			}
			$tax_query[$key][$item_value] = $item_count;
		} elseif ( $query_type === 'meta_query' ) {
			if ( ! isset( $meta_query[$key] ) ) {
				$meta_query[$key] = array();
			}
			$meta_query[$key][$item_value] = $item_count;
		}
	}

	$results_arr = array(
		'tax_query' => $tax_query,
		'meta_query' => $meta_query
	);

	// New Code
	$response['success'] = true;
	$response['results'] = array(
		'total_count'   => $total_count,
		'queried_count' => $filter_enable_pagination ? "1-" . $product_count : $product_count,
	);
	$response['indexing'] = $results_arr;


	return wp_send_json( $response );
	wp_die();
}

// Check if array is empty or contains only empty arrays
function gspb_is_effectively_empty( $array ) {    
    return empty( $array ) || ! array_filter( $array, function($value) {
        return ! empty( $value );
    });
}

// Filters out keys that contain a specified delimiter.
function gspb_filter_keys_with_delimiter( $keys, $delimiter ) {
    return array_filter($keys, function($key) use ($delimiter) {
        return strpos($key, $delimiter) === false;
    });
}

/**
 * Retrieves the count of terms and meta values for a given post type and filters.
 * 
 */
function gspb_get_filtered_counts($post_type = "", $filterd_tax_query = array(), $filterd_meta_query = array(), $exclude_taxonomy = array(), $include_taxonomy = array()) {
    $data = array();
    $taxo_array = array();
    $excluded_term_ids = array();
    $included_term_ids = array();

    $exclude_taxo = '';
    $exclude_terms = array();
    $include_taxo = '';
    $include_terms = array();
    // Check if either exclude or include taxonomy arrays are not empty
    if (!empty($exclude_taxonomy) || !empty($include_taxonomy)) {
        // Set exclude variables if exclude taxonomy exists
        if (!empty($exclude_taxonomy['taxonomy']) && !empty($exclude_taxonomy['terms'])) {
            $exclude_taxo = $exclude_taxonomy['taxonomy'];
            $exclude_terms = $exclude_taxonomy['terms'];
        }

        // Set include variables if include taxonomy exists
        if (!empty($include_taxonomy['taxonomy']) && !empty($include_taxonomy['terms'])) {
            $include_taxo = $include_taxonomy['taxonomy'];
            $include_terms = $include_taxonomy['terms'];
        }
    }

    // Get excluded term IDs
    if (!empty($exclude_taxo) && !empty($exclude_terms)) {
        $exclude_slugs = $exclude_terms;
        foreach ($exclude_slugs as $slug) {
            $term = get_term_by('slug', $slug, $exclude_taxo);
            if ($term) {
                $excluded_term_ids[$exclude_taxo][] = $term->term_id;
            }
        }
    }

    // Get included term IDs
    if (!empty($include_taxo) && !empty($include_terms)) {
        $include_slugs = $include_terms;
        foreach ($include_slugs as $slug) {
            $term = get_term_by('slug', $slug, $include_taxo);
            if ($term) {
                $included_term_ids[$include_taxo][] = $term->term_id;
            }
        }
    }

    // Build tax query for filtering posts
    $tax_query = array('relation' => 'AND');

    // Add exclude condition
    if (!empty($excluded_term_ids[$exclude_taxo])) {
        $tax_query[] = array(
            'taxonomy' => $exclude_taxo,
            'field' => 'term_id',
            'terms' => $excluded_term_ids[$exclude_taxo],
            'operator' => 'NOT IN'
        );
    }

    // Add include condition
    if (!empty($included_term_ids[$include_taxo])) {
        $tax_query[] = array(
            'taxonomy' => $include_taxo,
            'field' => 'term_id',
            'terms' => $included_term_ids[$include_taxo],
            'operator' => 'IN'
        );
    }

    // Get filtered posts
    $args = array(
        'post_type' => $post_type,
        'fields' => 'ids',
        'posts_per_page' => -1,
        'tax_query' => $tax_query
    );

    $filtered_posts = get_posts($args);

    // taxonomy 
    $taxonomies = array_keys($filterd_tax_query);
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        ));

        $taxo_array[$taxonomy] = array();
        foreach ($terms as $term) {
            $term_id = $term->term_id;

            // Skip if this is an excluded term
            if ($taxonomy === $exclude_taxo && in_array($term_id, $excluded_term_ids[$exclude_taxo])) {
                continue;
            }

            // Count posts that have this term AND are in the filtered posts
            $term_posts = get_posts(array(
                'post_type' => $post_type,
                'fields' => 'ids',
                'posts_per_page' => -1,
                'post__in' => $filtered_posts,
                'tax_query' => array(
                    array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term_id
                    )
                )
            ));

            // Only add terms that have posts after filtering
            if (!empty($term_posts)) {
                $taxo_array[$taxonomy][$term_id] = count($term_posts);
            }
		}
	}

	// Meta
	global $wpdb;
	$meta_keys = array_keys( $filterd_meta_query );
	$meta_array = array();

	$filterd_meta_key = gspb_filter_keys_with_delimiter( $meta_keys, '|' );
	foreach ( $filterd_meta_key as $meta_key ) {
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT meta_value, COUNT(*) as count
             FROM $wpdb->postmeta
             WHERE meta_key = %s
             GROUP BY meta_value",
            $meta_key
        ) );

        $meta_array[$meta_key] = [];

        foreach ($results as $row) {
			if(! empty($row->meta_value)){
				$meta_array[$meta_key][$row->meta_value] = (int) $row->count;
			}
        }
    }

    $data = array(
        'tax_query' => $taxo_array,
        'meta_query' => $meta_array
    );

    return $data;
}

/**
 * Retrieves the IDs of posts that match the given filters.
 * 
 * This function constructs a WordPress query based on provided post type, taxonomy filters, 
 * meta filters, and a search keyword. It returns an array of unique post IDs that meet 
 * the specified criteria.
 */
function gspb_get_queried_ids( $post_type, $filtered_taxquery = array(), $filtered_metaquery = array(), $search_key = "", $tax_operator = "", $exclude_taxonomy = array(), $include_taxonomy = array() ) {
    $post_ids = array();
	$tax_query = array();
    $args = array(
        'post_type'      => $post_type,
        'fields'         => 'ids', 
        'posts_per_page' => -1,
    );	

	if( ! empty( $search_key ) ) {
		$args['s'] = $search_key;
	}

	// Add exclude taxonomy if it's not empty
	if (!empty($exclude_taxonomy) && !empty($exclude_taxonomy['terms'])) {
		$tax_query[] = array(
			'taxonomy' => $exclude_taxonomy['taxonomy'],
			'field'    => $exclude_taxonomy['field'],
			'terms'    => $exclude_taxonomy['terms'],
			'operator' => $exclude_taxonomy['operator']
		);
	}

	// Add include taxonomy if it's not empty
	if (!empty($include_taxonomy) && !empty($include_taxonomy['terms'])) {
		$tax_query[] = array(
			'taxonomy' => $include_taxonomy['taxonomy'],
			'field'    => $include_taxonomy['field'],
			'terms'    => $include_taxonomy['terms'],
			'operator' => $include_taxonomy['operator']
		);
	}

	// Build the tax_query if provided
    if ( ! empty( $filtered_taxquery ) ) {
	
		foreach ( $filtered_taxquery as $taxonomy => $terms ) {
			if(! empty($terms)) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $terms,
					'operator' => $tax_operator
					);
			}
	
			if ( ! empty( $tax_query ) ) {
				$args['tax_query'] = array(
					'relation' => 'AND',
				);
	
				$args['tax_query'] = array_merge( $args['tax_query'], $tax_query );
			}
			
		}
    }

    // Build the meta_query if provided
    if ( ! empty( $filtered_metaquery ) ) {
        $meta_query = array();
        foreach ( $filtered_metaquery as $meta_key => $metas ) {
			if( ! empty( $metas ) ) {
				if ( strpos( $meta_key, '|' ) ) {
					$metakey_data = explode( '|', $meta_key );
					switch ( $metakey_data[1] ) {
						case 'range':
							$min_max = $metas['minmax'];
							$explode_minmax = explode('|', $min_max);
							$meta_query[] = array(
								'key'     => $metakey_data[0],
								'value'   => [$explode_minmax[0], $explode_minmax[1]],
								'compare' => 'BETWEEN',
								'type'    => "numeric"
							);
						break;
					}

				} else {
					$meta_query[] = array(
						'key'     => $meta_key,
						'value'   => $metas,
						'compare' => 'IN',
					);

					// New Code
					if( '_wc_average_rating' === $meta_key ) {
						$meta_query[array_key_last( $meta_query )]['type'] = 'NUMERIC';
					}
				}
			}
        }

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = array(
				'relation' => 'AND',
			);
			
			$args['meta_query'] = array_merge( $args['meta_query'], $meta_query );
		}
    }

	$term_posts = get_posts( $args );
    $post_ids = array_merge( $post_ids, $term_posts );

    return array_unique($post_ids);
}

/**
 * Retrieves all unique terms and meta values based on provided taxonomy and meta filters.
 * 
 * This function queries the database to retrieve distinct terms for specified taxonomies and
 * distinct meta values for specified meta keys. It returns an array with the results categorized
 * into taxonomy and meta query results.
 */
function gspb_get_unique_terms_and_meta_values( $post_type = "", $filterd_tax_query = array(), $filterd_meta_query = array() ){
	$data = array();
	$taxo_array = array();
	$meta_array = array();
	global $wpdb;

	// Taxonomy
	$taxonomies = array_keys( $filterd_tax_query );
	foreach( $taxonomies as $taxonomy  ) {
		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		));
		
		$taxo_array[$taxonomy] = array_unique($terms);
	}

	// Meta Query
	$meta_keys = array_keys( $filterd_meta_query );
	$filterd_meta_key = gspb_filter_keys_with_delimiter($meta_keys, '|');
	$meta_values = array();

	$post_meta_table = $wpdb->prefix.'postmeta';
	foreach ( $filterd_meta_key as $meta_key ) {
		$sql = $wpdb->prepare(
			"SELECT DISTINCT meta_key, meta_value
			 FROM {$post_meta_table}
			 WHERE meta_key = %s",
			$meta_key
		);
		$results = $wpdb->get_results($sql);
		$meta_values[$meta_key] = array();

		foreach ( $results as $result ) {
			$meta_values[$meta_key][] = $result->meta_value;
		}
	}

	$data = array(
		'tax_query'  => $taxo_array,
		'meta_query' => $meta_values
	);

	return $data;
}

/**
 *  Handles the REST API request to index posts based on the provided parameters.
 * 
 * This function processes the request to index posts of a specific post type, handles pagination.
 * 
 *  @param WP_REST_Request $request The request object containing the parameters for indexing.
 */
function gspb_handle_indexing_callback( WP_REST_Request $request ) {
	$start_time = microtime(true); // Start time
	$params     = $request->get_params();
	
	$post_type    = isset( $params['postType'] ) ? $params['postType'] : "";
	$current_page = isset( $params['currentpage'] ) ? (int) $params['currentpage'] : 1;
	$limit        = isset( $params['limit'] ) ? (int) $params['limit'] : 10;

	$response = array();

	if ( empty( $post_type ) ) {
		$response['success'] = false;
		$response['message'] = "Please provide Post Type.";

		wp_send_json( $response );
        wp_die();
	}

	$replace_key     = str_replace( '-', '_', $post_type );
	$indexer_key     = $replace_key.'_indexer';
	$get_indexer_key = get_option( $indexer_key );

	if ( ! $get_indexer_key ) {
		add_option( $indexer_key, true );
	}

	$taxonomies      = get_object_taxonomies( $post_type );
	$indexing_result = gspb_index_posts( $post_type, $taxonomies, $current_page, $limit, $get_indexer_key );

	$end_time = microtime(true);

	$response['success']  = true;
	$response['data']     = $indexing_result;
	$response['duration'] = $end_time - $start_time;

	wp_send_json( $response );
	wp_die();
}


/**
 * Indexes posts for a given post type, handling both taxonomy and meta fields.
 *
 * @param string  $post_type         Post type to index.
 * @param array   $taxonomies        Taxonomies to include (currently unused in loop).
 * @param int     $page              Current batch page.
 * @param int     $limit             Number of posts per batch.
 * @param boolean $get_indexer_key   Whether to reset the indexer on first page.
 *
 * @return array Indexing result (failed records and pagination info).
 */
function gspb_index_posts( $post_type, $taxonomies, $page, $limit, $get_indexer_key ) {

	global $wpdb;
	$indexer_table = $wpdb->prefix.'gspb_filters_indexer';

	$offset        = ( $page - 1 ) * $limit;
	$indexer_table = $wpdb->prefix . 'gspb_filters_indexer';

	// Clear previous indexed data only on the first batch.
	if ( 1 === (int) $page && $get_indexer_key ) {
		gspb_clear_already_indexed_data( $post_type );
	}

	// Query posts args.
	$args = array(
		'post_type'              => $post_type,
		'posts_per_page'         => $limit,
		'offset'                 => $offset,
		'fields'		         => 'ids',
		'post_status'            => array( 'publish', 'private' ),
		'order'                  => 'ASC',
		'orderby'                => 'date',
		'no_found_rows'          => true,	// Skip SQL_CALC_FOUND_ROWS
		'cache_results'          => true,	// Use object cache
		'update_post_term_cache' => false,	// Don't preload term cache (not needed for IDs)
		'update_post_meta_cache' => false,	// Don't preload meta cache (not needed for IDs)
		'suppress_filters'       => true,	// Bypass extra filters for speed
	);

	$posts_query = new WP_Query( $args );
	$posts       = $posts_query->posts;

	$res = array();
	if ( empty( $posts ) ) {
		$res['sucess']  = false;
		$res['message'] = "No posts founds.";

		return $res;
    }

	$failed_records   = array();
	$inserted_records = array();

	foreach ( $posts as $post_id ) {
		// Taxonomy Indexing
		$tax_indexing_result = gspb_taxonomy_indexer( $post_id, $post_type, $indexer_table );

		// Failed Records.
		if ( ! empty( $tax_indexing_result['failed_records'] ) ) {
			array_push( $failed_records, ...$tax_indexing_result['failed_records'] );
		}

		if ( ! empty( $tax_indexing_result['inserted_records'] ) ) {
			array_push( $inserted_records, ...$tax_indexing_result['inserted_records'] );
		}

		// Meta Indexing
		$meta_indexing_result = gspb_meta_indexer( $post_id, $post_type, $indexer_table );

		// Failed Records.
		if ( ! empty( $meta_indexing_result['failed_records'] ) ) {
			array_push( $failed_records, ...$meta_indexing_result['failed_records'] );
		}

		// Inserted Records.
		if ( ! empty( $meta_indexing_result['inserted_records'] ) ) {
			array_push( $inserted_records, ...$meta_indexing_result['inserted_records'] );
		}
	}

	$has_more_pages = count( $posts ) === $limit;

	$res = array(
		'failedRecords' => $failed_records,
		'morePages'     => $has_more_pages
	);

	return $res;
}

/**
 * Clears previously indexed data for a specific post type from the indexer table.
 * 
 * @param string $post_type The post type for which indexed data should be cleared.
 */
function gspb_clear_already_indexed_data( $post_type ) {
	global $wpdb;
	
	$sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}gspb_filters_indexer WHERE `type` = %s", $post_type );
    $wpdb->query( $sql );
}

/**
 * Indexes taxonomy terms for a given post and records them in the indexer table.
 * 
 * @param int $post_ID The ID of the post whose taxonomy terms are being indexed.
 * @param string $post_type The post type of the post being indexed.
 * @param string $indexer_table The name of the database table where indexer data is stored.
 * 
 */
function gspb_taxonomy_indexer( $post_ID, $post_type, $indexer_table ) {
    global $wpdb;

    $failed_records   = array();
    $inserted_records = array();

	$taxonomies = gspb_get_taxonomies_for_type( $post_type );

	if ( empty( $taxonomies ) ) {
		return array(
			'failed_records'   => $failed_records,
			'inserted_records' => $inserted_records
		);
	}

    foreach ( $taxonomies as $taxonomy ) {
        $terms = get_the_terms( $post_ID, $taxonomy );

		if ( empty( $terms ) ) {
			continue;
		}

        foreach ( $terms as $term ) {
			$term_id = $term->term_id;

            $data_row = array(
                'type'       => $post_type,
                'item_id'    => $post_ID,
                'item_query' => 'tax_query',
                'item_key'   => $taxonomy,
                'item_value' => $term_id
            );

            $is_inserted = $wpdb->insert(
                $indexer_table,
                $data_row,
                array( '%s', '%d', '%s', '%s', '%d' )
            );

            if ( false === $is_inserted ) {
                $failed_records[] = $data_row;
            } else {
                $inserted_records[] = $data_row;
            }
        }
    }

    return array(
        'failed_records'   => $failed_records,
        'inserted_records' => $inserted_records
    );
}

/**
 * Indexes meta data for a given post and records them in the indexer table.
 * 
 * @param int $post_ID The ID of the post whose meta data is being indexed.
 * @param string $post_type The post type of the post being indexed.
 * @param string $indexer_table The name of the database table where indexer data is stored.
 */
function gspb_meta_indexer( $post_ID, $post_type, $indexer_table ){
	global $wpdb;
    $meta_keys = get_post_custom_keys( $post_ID );
    $failed_records = array();
    $inserted_records = array();

    if ( ! empty( $meta_keys ) ) {
        foreach ( $meta_keys as $meta_key ) {
            // Skip meta keys that start with an underscore
            if ( strpos( $meta_key, '_' ) === 0 && $meta_key !== '_stock_status' && $meta_key !== '_wc_average_rating' ) { // New Code
                continue;
            }

            $meta_values = get_post_meta( $post_ID, $meta_key, false );
            foreach ( $meta_values as $meta_value ) {

				if ( $meta_value ){
					$data_row = array(
						'type'       => $post_type,
						'item_id'    => $post_ID,
						'item_query' => 'meta_query',
						'item_key'   => $meta_key,
						'item_value' => maybe_serialize( $meta_value )
					);

					$is_inserted = $wpdb->insert(
						$indexer_table,
						$data_row,
						array( '%s', '%d', '%s', '%s', '%s' )
					);

					if ( false === $is_inserted ) {
						$failed_records[] = $data_row;
					} else {
						$inserted_records[] = $data_row;
					}
				}
            }
        }
    }

    return array(
        'failed_records'   => $failed_records,
        'inserted_records' => $inserted_records
    );
}


/**
 * Removes entries from the indexer table when a post is deleted.
 * 
 * @param int $post_id The ID of the post being deleted.
 * @param WP_Post $post The post object being deleted.
 */
// add_action( 'wp_trash_post', 'gspb_remove_data_from_indexer', 10, 1 );
add_action( 'before_delete_post', 'gspb_remove_data_from_indexer', 10, 2 );
function gspb_remove_data_from_indexer( $post_id, $post ) {
	global $wpdb;
	$indexer_table = $wpdb->prefix.'gspb_filters_indexer';

    if ( ! is_numeric( $post_id ) || $post_id <= 0 ) {
        return;
    }

    // Check if indexer table exists before attempting delete
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$indexer_table'");
    if (!$table_exists) {
        return;
    }

    $wpdb->delete( $indexer_table, array( 'item_id' => $post_id ), array( '%d' ) );
}

/**
 * Callback function triggered after a post is inserted or updated.
 * 
 * @param int $post_ID The ID of the post being inserted or updated.
 * @param WP_Post $post The post object being inserted or updated.
 * @param bool $update Boolean indicating whether this is an update to an existing post.
 */
// add_action( 'save_post', 'gspb_post_updated_callback', 10, 3 );
add_action( 'wp_after_insert_post', 'gspb_post_updated_callback', 10, 3 );
function gspb_post_updated_callback( $post_ID, $post, $update ) {
    // Check if this is an autosave or revision
    if ( wp_is_post_autosave( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
        return;
    }

    if ( $post->post_status === 'auto-draft' ) {
        return;
    }

    global $wpdb;
    $indexer_table = $wpdb->prefix . 'gspb_filters_indexer';

    // Check if indexer table exists before attempting delete
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$indexer_table'");
    if (!$table_exists) {
        return;
    }

    // Delete existing entries for this post ID to avoid duplicates
    $wpdb->delete(
        $indexer_table,
        array( 'item_id' => $post_ID ),
        array( '%d' )
    );

    $post_type = $post->post_type;

    $replace_key = str_replace( '-', '_', $post_type );
    $indexer_key = $replace_key . '_indexer';
    $get_indexer_key = get_option( $indexer_key );

	$failed_records = array();
	$inserted_records = array();

    if ( $get_indexer_key ) {
		// Handler taxonomy terms
		$tax_indexing_result = gspb_taxonomy_indexer( $post_ID, $post_type, $indexer_table );
        $failed_records = array_merge( $failed_records, $tax_indexing_result['failed_records'] );
        $inserted_records = array_merge( $inserted_records, $tax_indexing_result['inserted_records'] );
       
		// Handler Meta Fields
		$meta_indexing_result = gspb_meta_indexer( $post_ID, $post_type, $indexer_table );
        $failed_records = array_merge( $failed_records, $meta_indexing_result['failed_records'] );
        $inserted_records = array_merge( $inserted_records, $meta_indexing_result['inserted_records'] );
    }

}

// Hook the function to the 'delete_term' action
add_action( 'delete_term', 'gspb_remove_term_from_indexer', 10, 3 );

/**
 * Handles the removal of indexer table entries when a taxonomy term is deleted.
 *
 * @param int $term_id The ID of the term that was deleted.
 * @param int $tt_id The ID of the taxonomy that was deleted.
 * @param string $taxonomy The taxonomy name of the term that was deleted.
 */
function gspb_remove_term_from_indexer( $term_id, $tt_id, $taxonomy ) {
    global $wpdb;

    $indexer_table = $wpdb->prefix . 'gspb_filters_indexer';

    // Check if indexer table exists before attempting delete
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$indexer_table'");
    if (!$table_exists) {
        return;
    }

    $wpdb->delete(
        $indexer_table,
        array( 'item_key' => $taxonomy, 'item_value' => $term_id ),
        array( '%s', '%s' ) 
    );
}

// Function to modify the query clauses for sorting products by average rating | New Code
function gspb_sorting_product_average_rating_clauses( $clauses, $query ) {
    global $wpdb;

    if ( isset( $query->query['post_type'] ) && $query->query['post_type'] == 'product' ) {
        // Custom JOIN with the product meta lookup table
        $clauses['join'] .= " LEFT JOIN {$wpdb->prefix}wc_product_meta_lookup wc_product_meta_lookup ON {$wpdb->prefix}posts.ID = wc_product_meta_lookup.product_id ";

        // Get all term IDs for the 'product_visibility' taxonomy
        $terms = get_terms(
			array(
				'taxonomy'   => 'product_visibility',
				'fields'     => 'ids',  // Get only term IDs
				'hide_empty' => true,  // Include terms even if no products are assigned to them
				'exclude'    => array(
					get_term_by( 'slug', 'exclude-from-search', 'product_visibility' )->term_id,
					get_term_by( 'slug', 'featured', 'product_visibility' )->term_id,
					get_term_by( 'slug', 'outofstock', 'product_visibility' )->term_id,
					get_term_by( 'slug', 'rated-1', 'product_visibility' )->term_id,
					get_term_by( 'slug', 'rated-2', 'product_visibility' )->term_id,
					get_term_by( 'slug', 'rated-3', 'product_visibility' )->term_id,
					get_term_by( 'slug', 'rated-4', 'product_visibility' )->term_id,
					get_term_by( 'slug', 'rated-5', 'product_visibility' )->term_id,
				)
			)
		);

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            // Convert the array of term IDs to a comma-separated list
            $term_ids = implode( ',', $terms );

            // Exclude products based on the term IDs
            $clauses['where'] .= " AND {$wpdb->prefix}posts.ID NOT IN (
                SELECT object_id
                FROM {$wpdb->prefix}term_relationships
                WHERE term_taxonomy_id IN ($term_ids)
            ) ";
        }

        // Custom ORDER BY
        $clauses['orderby'] = "wc_product_meta_lookup.average_rating DESC, wc_product_meta_lookup.rating_count DESC, wc_product_meta_lookup.product_id DESC";
    }

    return $clauses;
}

// Function to modify the query clauses for sorting products by total sales | New Code
function gspb_sorting_product_total_sales_clauses( $clauses, $query ) {
    global $wpdb;

    if ( isset( $query->query['post_type'] ) && $query->query['post_type'] == 'product' ) {
       // Custom JOIN with the product meta lookup table
	   $clauses['join'] .= " LEFT JOIN {$wpdb->prefix}wc_product_meta_lookup wc_product_meta_lookup ON {$wpdb->prefix}posts.ID = wc_product_meta_lookup.product_id ";

	   // Get all term IDs for the 'product_visibility' taxonomy
	   $terms = get_terms( array(
		   'taxonomy'   => 'product_visibility',
		   'fields'     => 'ids',  // Get only term IDs
		   'hide_empty' => true,  // Include terms even if no products are assigned to them
		   'exclude'    => array(
			   get_term_by( 'slug', 'exclude-from-search', 'product_visibility' )->term_id,
			   get_term_by( 'slug', 'featured', 'product_visibility' )->term_id,
			   get_term_by( 'slug', 'outofstock', 'product_visibility' )->term_id,
			   get_term_by( 'slug', 'rated-1', 'product_visibility' )->term_id,
			   get_term_by( 'slug', 'rated-2', 'product_visibility' )->term_id,
			   get_term_by( 'slug', 'rated-3', 'product_visibility' )->term_id,
			   get_term_by( 'slug', 'rated-4', 'product_visibility' )->term_id,
			   get_term_by( 'slug', 'rated-5', 'product_visibility' )->term_id,
		   )
	   ) );

	   if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		   // Convert the array of term IDs to a comma-separated list
		   $term_ids = implode( ',', $terms );

		   // Exclude products based on the term IDs
		   $clauses['where'] .= " AND {$wpdb->prefix}posts.ID NOT IN (
			   SELECT object_id
			   FROM {$wpdb->prefix}term_relationships
			   WHERE term_taxonomy_id IN ($term_ids)
		   ) ";
	   }

	   // Custom ORDER BY
	   $clauses['orderby'] = "wc_product_meta_lookup.total_sales DESC, wc_product_meta_lookup.product_id DESC";
    }

    return $clauses;
}	

/**
 * Retrieves unique WooCommerce product ratings. | New Code
 *
 * This function calls `gspb_get_ratings()` to fetch distinct `_wc_average_rating` values 
 * from the WooCommerce products and returns them in a structured response.
 *
 * @return array An associative array containing success status and ratings data.
 */
function gspb_get_wc_ratings(){
	$response = array();

	$ratings = gspb_get_ratings();

	$response['success'] = true;
	$response['ratings'] = $ratings;

	return $response;	
}


/**
 * Retrieves the taxonomies for a given post type.
 * 
 * @param string $post_type The post type to retrieve taxonomies for.
 * @return array The taxonomies for the given post type.
 */
function gspb_get_taxonomies_for_type( $post_type ) {
	static $cache = array(); // This will declare only once per request and persist across multiple function calls.

	if ( ! isset( $cache[ $post_type ] ) ) {
		$cache[ $post_type ] = get_object_taxonomies( $post_type );
	}

	return $cache[ $post_type ];
}



//////////////////////////////////////////////////////////////////
// Filter Indexer Activator
//////////////////////////////////////////////////////////////////
register_activation_hook( __FILE__, 'gspb_filter_filters_activation' );
// Register the deactivation hook to remove the table
register_deactivation_hook( __FILE__, 'gspb_filter_filters_deactivation' );

/**
 * Creates the indexer table on plugin activation.
 */
function gspb_filter_filters_activation() {
    global $wpdb;

    $table_full_name = "{$wpdb->prefix}gspb_filters_indexer";
    $wpdb->query( "DROP TABLE IF EXISTS {$table_full_name}" );

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_full_name (
        id int(10) unsigned NOT NULL AUTO_INCREMENT,
        type varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        item_id int(10) unsigned DEFAULT NULL,
        item_query varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        item_key varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        item_value varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        PRIMARY KEY (id),
        KEY `id` (`id`),
        KEY `item_id` (`item_id`),
        KEY `type` (`type`)
      ) $charset_collate";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );

}

/**
 * Drop the database table on plugin deactivation.
 */
function gspb_filter_filters_deactivation() {
    global $wpdb;

    $table_full_name = "{$wpdb->prefix}gspb_filters_indexer";
    $wpdb->query( "DROP TABLE IF EXISTS {$table_full_name}" );
}

/**
 * Retrieves the minimum and maximum price values for WooCommerce products.
 * 
 * This function checks if WooCommerce is active, and if so, queries the database 
 * to fetch the minimum and maximum price values from the `_price` meta key.
 *
 * @return array An array containing the minimum and maximum price, along with a success status.
*/
function gspbMinMaxPrice(){
    // Check if WooCommerce class exists
    if (!class_exists('WooCommerce')) {
        return [
            'success' => false,
            'message' => 'WooCommerce not active'
        ];
    }

    global $wpdb;
    
    // Use prepare to safely construct query
    $sql = $wpdb->prepare(
        "SELECT 
            MIN(CAST(meta_value AS DECIMAL(10,2))) AS min_price,
            MAX(CAST(meta_value AS DECIMAL(10,2))) AS max_price 
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s 
        AND pm.meta_value != ''
        AND p.post_type = %s
        AND p.post_status = %s",
        '_price',
        'product',
        'publish'
    );

    $result = $wpdb->get_row($sql, ARRAY_A);

    if(is_null($result)) {
        return [
            'success' => false,
            'message' => 'No price data found'
        ];
    }

    $result['success'] = true;
    $result['min_price'] = (float)$result['min_price'];
    $result['max_price'] = (float)$result['max_price'];

    return $result;
}


/**
 * Retrieves unique WooCommerce product ratings. | New Code
 *
 * This function queries the database to fetch distinct `_wc_average_rating` values 
 * from the WooCommerce products and returns them in a structured array.
 *  
 * @return array An array containing the ratings data.
*/
function gspb_get_ratings() {
    global $wpdb;

    // Get unique ratings from products
    $ratings = $wpdb->get_col("
        SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_wc_average_rating' 
        AND meta_value > 0
        ORDER BY meta_value DESC
    ");

    if (empty($ratings)) {
        return [];
    }

    $rating_options = array_map(function($rating) {
        return [
            'label' => "Rated " . (int)$rating . " out of 5",
            'value' => $rating,
            'slug' => $rating,
            'postCount' => '',
        ];
    }, $ratings);

    return $rating_options;
}