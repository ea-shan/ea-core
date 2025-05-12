<?php
/**
 * Filter Results Count Block
 *
 * This block handles the rendering of the results count for WooCommerce filters.
 * It displays the current number of results being shown and the total number of results.
 */

namespace greenshiftaddon\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter Results Count Class
 */
class FilterResultsCount {
	/**
	 * @var array $attributes The attributes for the Filter Results Count block.
	 * Attributes
	 */
	protected $attributes = array(
		'dynamicGClasses'    => array(
			'type'    => 'array',
			'default' => array(),
		),
		'id'                 => array(
			'type'    => 'string',
			'default' => null,
		),
		'inlineCssStyles'    => array(
			'type'    => 'string',
			'default' => '',
		),
		'animation'          => array(
			'type'    => 'object',
			'default' => array(),
		),
		'interactionLayers'  => array(
			'type'    => 'array',
			'default' => array(),
		),
		'filterConnectionId' => array(
			'type'    => 'string',
			'default' => '',
		),
		'labels' => array(
			'type'    => 'object',
			'default' => array(
				'showing' => 'Showing',
				'of' => 'of',
				'results' => 'Results',
			),
		),
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_handler' ) );
	}

	/**
	 * Initialize the Filter Results Count block
	 */
	public function init_handler() {
		register_block_type(
			__DIR__,
			array(
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => $this->attributes,
			)
		);
	}

	/**
	 * Render the Filter Results Count block
	 *
	 * @param array  $settings The settings for the block.
	 * @return string The rendered HTML for the block.
	 */
	public function render_block( $settings = array() ) {
		extract( $settings );

		$final_attrs_arr    = array();
		$block_id           = 'gspb_id-' . $id;
		$data_attributes    = gspb_getDataAttributesfromDynamic( $settings );
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $block_id . ' gspb-filterblock',
				...$data_attributes,
			)
		);

		$attributes_arr = array(
			'data_attributes'    => $data_attributes,
			'wrapper_attributes' => $wrapper_attributes,
			'animation'          => $animation,
			'interactionLayers' => $interactionLayers,
		);

		$html = '';

		$showing = esc_attr($labels['showing']);
		$of = esc_attr($labels['of']);
		$results = esc_attr($labels['results']);

		$html .= '<div id="' . esc_attr( $block_id ) . '" ' . $attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps( $attributes_arr['animation'], $attributes_arr['interactionLayers'] ) . '>';
		$html .= '<div class="gs-filters-results gs-filters-result-count_' . esc_attr( $filterConnectionId ) . '" data-connection="' . esc_attr( $filterConnectionId ) . '">' . $showing . ' <span class="gs-filters-result-current">x</span> ' . $of . ' <span class="gs-filters-result-all">x</span> ' . $results . '</div>';
		$html .= '</div>';

		return $html;
	}
}

new FilterResultsCount();
