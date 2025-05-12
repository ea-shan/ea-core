<?php
/**
 * Filter Sorting Block
 *
 * This block handles the rendering of a sorting filter for WooCommerce products.
 * It provides a dropdown to select sorting options, including WooCommerce default
 * sorting and custom sorting options.
 */

namespace greenshiftaddon\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter Sorting Class
 */
class FilterSorting {
	/**
	 * @var array $attributes The attributes for the Filter Results Count block.
	 * Attributes
	 */
	protected $attributes = array(
		'dynamicGClasses'      => array(
			'type'    => 'array',
			'default' => array(),
		),
		'id'                   => array(
			'type'    => 'string',
			'default' => null,
		),
		'inlineCssStyles'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'animation'            => array(
			'type'    => 'object',
			'default' => array(),
		),
		'interactionLayers'    => array(
			'type'    => 'array',
			'default' => array(),
		),
		'filterConnectionId'   => array(
			'type'    => 'string',
			'default' => '',
		),
		'sortingType'          => array(
			'type'    => 'string',
			'default' => 'woocommerceOpt',
		),
		'customSortingOptions' => array(
			'type'    => 'array',
			'default' => array(),
		),
		'labels' => array(
			'type'    => 'object',
			'default' => array(
				'default' => 'Default Sorting',
				'popularity' => 'Sort by popularity',
				'rating' => 'Sort by average rating',
				'date' => 'Sort by latest',
				'price' => 'Sort by price: low to high',
				'price-desc' => 'Sort by price: high to low',
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
	 * Initialize the Filter Sorting block
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
			'interactionLayers'  => $interactionLayers,
		);

		ob_start();
		switch ( $sortingType ) {
			case 'woocommerceOpt':
				$options = $this->render_select_options( 'woocommerceOpt', $customSortingOptions, $labels );
				$html    = $this->gspbRenderSelectControl( $block_id, $filterConnectionId, $options, 'woo_sorting', $attributes_arr );
				break;
			case 'customOpt':
				$options = $this->render_select_options( 'customOpt', $customSortingOptions );
				$html    = $this->gspbRenderSelectControl( $block_id, $filterConnectionId, $options, 'custom_sorting', $attributes_arr );
				break;
		}

		echo $html;  // Echo the HTML before getting clean buffer.
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Renders sorting options for the select dropdown based on the specified type.
	 *
	 * This function generates an array of sorting options either for WooCommerce default sorting
	 * or custom sorting options. For WooCommerce, it includes standard options like popularity,
	 * rating, date, and price sorting. For custom options, it processes user-defined sorting criteria.
	 *
	 * @param string $type The type of sorting options.
	 * @param array $customSortingOptions The custom sorting options.
	 * @return array The array of sorting options.
	 */
	public function render_select_options( $type, $customSortingOptions = array(), $labels = array() ) {
		$options = array();

		if( 'woocommerceOpt' === $type ) {
			$options = array(
				array(
					'value' => 'default',
					'label' => esc_attr( $labels['default'] ),
					'key'   => 'default',
				),
				array(
					'value' => 'popularity',
					'label' => esc_attr( $labels['popularity'] ),
					'key'   => 'popularity',
				),
				array(
					'value' => 'rating', 
					'label' => esc_attr( $labels['rating'] ),
					'key'   => 'rating',
				),
				array(
					'value' => 'date',
					'label' => esc_attr( $labels['date'] ),
					'key'   => 'date',
				),
				array(
					'value' => 'price',
					'label' => esc_attr( $labels['price'] ),
					'key'   => 'price',
				),
				array(
					'value' => 'price-desc',
					'label' => esc_attr( $labels['price-desc'] ),
					'key'   => 'price-desc',
				),
			);

		} elseif ( 'customOpt' === $type ) {

			if ( ! empty( $customSortingOptions ) ) {
				$options = array(
					array(
						'value' => 'default',
						'label' => esc_attr( $labels['default'] ),
						'key'   => 'default',
					),
				);
				foreach ( $customSortingOptions as $option ) {
					$name             = isset( $option['name'] ) ? $option['name'] : '';
					$value            = isset( $option['value'] ) ? $option['value'] : '';
					$label            = isset( $option['label'] ) ? $option['label'] : '';
					$custom_field_key = isset( $option['customFieldKey'] ) ? $option['customFieldKey'] : '';
					
					$value_name = '';

					if ( 'custom_field' === $name ) {
						$value_name = 'custom_field_' . $value;
					} else {
						$value_name = $name . '_' . $value;
					}

					// Format the display label as "label - value"
					$display_label = $label . ' - ' . strtoupper( $value );
					
					$options[] = array(
						'value' => $value_name,
						'label' => $display_label,
						'key'   => $custom_field_key ? $custom_field_key : $name,
					);
				}
			}
		}
		return $options;
	}

	/**
	 * Renders a select dropdown control for sorting functionality.
	 * @param string $block_id The ID of the block.
	 * @param string $filterConnectionId The ID of the filter connection.
	 * @param array $options The options for the select dropdown.
	 * @param string $type The type of sorting options.
	 * @param array $attributes_arr The attributes for the block.
	 * @return string The rendered HTML for the select dropdown.
	 */
	public function gspbRenderSelectControl( $block_id, $filterConnectionId, $options = array(), $type = 'woo_sorting', $attributes_arr = array() ) {
		$html  = '<div id="' . esc_attr( $block_id ).  '" class="gspb-select-control-sorting"' . esc_attr( $attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps( $attributes_arr['animation'], $attributes_arr['interactionLayers'] ) ) . '>';
		$html .= '<select id="' . $block_id . '" class="gspb-select-sorting gspb-select" data-connection="' . esc_attr( $filterConnectionId ) . '" data-filter="' . esc_attr( $type ) . '" data-key="gspbfilter">';

		foreach ( $options as $option ) {
			$html .= '<option value="' . esc_attr( $option['value'] ) . '" data-val="' . esc_attr( $option['value'] ) . '" data-key="' . esc_attr( $option['key'] ) . '">' . esc_html( $option['label'] ) . '</option>';
		}

		$html .= '</select>';
		$html .= '</div>';

		return $html;
	}
}

new FilterSorting();
