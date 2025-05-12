<?php
namespace greenshiftaddon\Blocks;

defined('ABSPATH') or exit;

class FilterBlock {

    public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

    public function init_handler(){
		register_block_type(__DIR__, array(
			'render_callback' => array( $this, 'render_block' ),
            'attributes'      => $this->attributes
		)
		);
	}

    protected $attributes = array(
        'dynamicGClasses' => array(
			'type' => 'array',
			'default' => []
		),
		'id' => array(
			'type'    => 'string',
			'default' => null,
		),
		'inlineCssStyles' => array(
			'type'    => 'string',
			'default' => '',
		),
		'animation' => array(
			'type' => 'object',
			'default' => array(),
		),
        'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
		),
        "resourceType" => array(
            "type"    => "string",
            "default" => "TEXONOMY"
        ),
        "taxonomy"  => array(
            "type"    => "object",
            "default" => null
        ),
        "designType" => array(
            "type"    => "string",
            "default" => ""
        ),
        "metaKey" => array(
            "type"    => "string",
            "default" => ""
        ),
        "metaOptions" => array(
            "type"    => "string",
            "default" => ""
        ),
        "minValue" => array(
            "type"    => "string",
            "default" => ""
        ),
        "maxValue" => array(
            "type"    => "string",
            "default" => ""
        ),
        "stepValue" => array(
            "type"    => "string",
            "default" => ""
        ),
        "filterId" => array(
            "type"  => "string",
            "default"   => ""
        ),
        "filterRelation" => array(
            "type"  => "string",
            "default"   => "AND"
        ),
        "filterOperator" => array(
            "type"    => "string",
            "default" => "IN"
        ),
        "includeChildTerms" => array(
            "type"     => "string",
            "default"  => "NO"
        ),
        "labels" => array(
            "type"     => "object",
            "default"  => array()
        ),
        // New Code: Search input box placeholder text
        'searchPlaceholderText' => array(
            'type'     => 'string',
            'default'  => ''
        )
    );

    public function render_block( $settings = array(), $inner_content='' ){
        extract($settings);
        $final_attrs_arr = array();
        $filterType = $resourceType === "TEXONOMY" ? "tax_query" : "meta_query";
        $filterOptions = [];
        $key = "";

        $filter_operator = $resourceType === "TEXONOMY" ? $filterOperator : "";

        $blockId = 'gspb_id-' . $id;
        $data_attributes = gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb-filterblock',
				...$data_attributes
			)
		);

        $attributes_arr = array(
            "data_attributes"   => $data_attributes,
            "wrapper_attributes"    => $wrapper_attributes,
            "animation" => $animation,
            "interactionLayers" => $interactionLayers,
            "labels" => $labels
        );

        if ( $resourceType === "TEXONOMY" &&  @$taxonomy['value'] ){
            $filterOptions = $this->get_terms_data( $taxonomy['value'], $includeChildTerms );
            $key = $taxonomy['value'];
        }

        if ( $resourceType === "CUSTOMMETA" &&  $designType !== "rangeslider" ){
            $filterOptions = $this->get_metaoptions( $metaOptions );
            $key = $metaKey;
        }

        // New Code: Woo Rating filter
        if ( "RATING" === $resourceType ) {
            $filterOptions = gspb_get_ratings();
            $key           = '_wc_average_rating';
        }

        ob_start();
        switch ( $resourceType ) {
            case 'TEXONOMY':
            case 'CUSTOMMETA':

                switch ( $designType ) {
                    case 'checkbox':
                        $data = $this->gspbCheckbox( $filterOptions, $filterType, $key, $filterId, $blockId, $attributes_arr, $filter_operator );
                        echo $data;
                        break;
                        case 'radio':
                            $data = $this->gspbRadio( $filterOptions, $filterType, $key, $filterId, $blockId, $attributes_arr, $filter_operator );
                            echo $data;
                            break;
                        case 'select':
                            $data = $this->gspbSelect( $filterOptions, $filterType, $key, $filterId, $blockId, $attributes_arr, $filter_operator );
                            echo $data;
                            break;
                        case 'tags':
                            $data = $this->gspbTags( $filterOptions, $filterType, $key, $filterId, $blockId, $attributes_arr, $filter_operator );
                            echo $data;
                            break;
                        case 'rangeslider':
                            $data = $this->gspbRangeSlider( $filterType, $minValue, $maxValue, $stepValue, $metaKey, $filterId, $blockId, $attributes_arr );
                            echo $data;
                            break;
                    default:
                        # code...
                        break;
                }
                break;
            case 'TITLE':
                if ( 'searchinput' === $designType ) {
                  $data = $this->gspbSearchInput( $filterId, $blockId, $attributes_arr, $searchPlaceholderText );
                  echo $data;
                }
                break;
            // Price and stock filter only works with WooCommerce
            case 'PRICE':
                $priceArr = gspbMinMaxPrice();
                
                if(!$priceArr['success']){
                    return "Wocommerce Plugin is not active!!";
                }

                $minValue = $priceArr['min_price'];
                $maxValue = $priceArr['max_price'];

                $data = $this->gspbRangeSlider( 'meta_query', $minValue, $maxValue, $stepValue, '_price', $filterId, $blockId, $attributes_arr );
                echo $data;
                break;
            case "STOCK":
                $stock_options = array(
                        array(
                            'label' => !empty($labels['instock']) ? $labels['instock'] : 'In Stock',
                            'value' => 'instock',
                            'slug' => 'instock',
                            'postCount' => '',
                        ),
                        array(
                            'label' => !empty($labels['outofstock']) ? $labels['outofstock'] : 'Out Of Stock',
                            'value' => 'outofstock',
                            'slug' => 'outofstock',
                            'postCount' => '',
                        ),
                        array(
                            'label' => !empty($labels['onbackorder']) ? $labels['onbackorder'] : 'On backorder',
                            'value' => 'onbackorder',
                            'slug' => 'onbackorder',
                            'postCount' => '',
                        )
                    );
               
                $data = $this->gspbCheckbox( $stock_options, 'meta_query', '_stock_status', $filterId, $blockId, $attributes_arr, $filter_operator );
                        echo $data;
            break;
            case 'RATING':
                $data = $this->gspbRating( $filterOptions, $filterType, $key, $filterId, $blockId, $attributes_arr, $filter_operator );
                echo $data;
                break;
            default:
                // default code
        }

        $output = ob_get_contents();
		ob_end_clean();
		return $output;
    }

    /**
     * Get Term lists
     *
     * @param array $taxonomy The taxonomy is retrive term lists.
     */
    public function get_terms_data( $taxonomy = '', $includeChildTerms = "NO" ) {

        $args = array(
            'taxonomy' => $taxonomy,
            // 'hide_empty' => true, // default is true
        );

        if ( "NO" === $includeChildTerms ) {
            $args['parent'] = 0;
        }

        $terms = get_terms( $args );

        if ( empty ( $terms ) ) {
            return [];
        }

        $parent_terms = [];
        $child_terms = [];
        $options = [];

        foreach( $terms as $term ){
            if ( $term->parent === 0 ) {
                $parent_terms[$term->term_id] = array(
                    'label'      => $term->name,
                    'value'      => $term->term_id,
                    'slug'       => $term->slug,
                    'postCount'  => $term->count,
                    'class'      => 'gs-parent',
                    'children'   => array()
                );
            } else {
                $child_terms[$term->term_id] = array(
                    'label'     => $term->name,
                    'value'     => $term->term_id,
                    'slug'      => $term->slug,
                    'postCount' => $term->count,
                    'class'     => "gs-child"
                );
            }
        }


        // Associate child terms with their parent
        foreach ( $child_terms as $child_id => $child ) {
            $parent_id = get_term($child_id)->parent;

            if ( isset($parent_terms[$parent_id]) ) {
                $parent_terms[$parent_id]['children'][] = $child;
            }
        }

        // Flatten the terms into the options array
        foreach ( $parent_terms as $parent ) {
            $options[] = $parent;
            if ( !empty($parent['children']) ) {
                $options = array_merge($options, $parent['children']);
            }
        }

        return $options;
    }

    /**
     * Get Term lists
     *
     * @param array $taxonomy The taxonomy is retrive term lists.
     */
    public function get_metaoptions( $meta_options = '' ) {

        $lines = explode("\n", $meta_options); // Split the content into lines
        $options = array();

        foreach ($lines as $line) {
            $line = trim($line); // Trim whitespace from the line
            if (!empty($line)) {
                // Split the line into key and value
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $options[] = ['label'=>$value, 'value'=> $key, 'slug' => $key, 'postCount' => '' ];
                }
            }
        }
        return $options;

    }

    /**
     * Checkbox Style
     */
    public function gspbCheckbox( $options = array(), $filter_type = '', $key = '', $filterId = '', $blockId = '', $attributes_arr = array(), $filter_operator = '' ) {
        $html = '';

        $html .= '<div data-type="' . $filter_type . '" data-query="' . $key . '" ' .$attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps($attributes_arr['animation'], $attributes_arr['interactionLayers']) . ' id="'. $blockId .'">';
            $html .= '<div class="gspb-filter-item gspb-checkbox-box">';
                if( ! empty( $options ) ){
                    foreach ($options as $option ) {
                        if(isset($option['postCount']) && 0 !== $option['postCount']) {
                            $forId = $blockId . '-' . $option['value'];
                            $forName = $blockId . '-' .$option['slug'];
                            $optionClass = isset($option['class']) ? $option['class'] : '';
                            $html .= '<div class="gspb-checkbox__item gspb-item ' . $optionClass . '">';
                            $html .= '<label for="' . $forId. '" class="gspb-checkbox gspb-checkbox__style">';
                            $html .= '<input type="checkbox" id="' . $forId . '" name="' . $forName .'" value="'. $option['slug'] .'" data-filter="' . $filter_type . '" data-slug="' . $key . '" data-key="gspbfilter" data-val="' . $option['value'] . '" data-connection="' . $filterId . '" data-operator="' . $filter_operator . '" />';
                            $html .= '<div class="gspb-checkbox__checkmark"></div>';
                            $html .= '<div class="gspb-checkbox__body">'. $option['label'] .'</div>';
                            $html .= '</label>';
                            $html .= '<span class="gs-filters-counter"></span>';
                            $html .= '</div>';
                        }
                    }
                }
                $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Radio Style
     */
    public function gspbRadio( $options = array(), $filter_type = '', $key = '', $filterId = '', $blockId = '', $attributes_arr = array(), $filter_operator = '' ) {
        $html = '';
        $html .= '<div data-type="' . $filter_type . '" data-query="' . $key . '" ' .$attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps($attributes_arr['animation'], $attributes_arr['interactionLayers']) . ' id="'. $blockId .'">';
            $html .= '<div class="gspb-filter-item gspb-radio-box">';
            if( ! empty( $options ) ){
                foreach ($options as $option) {
                    $forId = $blockId . '-' . $option['slug'];
                    $uniqueName = $blockId . '-' . $key;
                    $optionClass = isset($option['class']) ? $option['class'] : '';
                    $html .= '<div class="gspb-radio__item gspb-item ' . $optionClass . '">';
                        $html .= '<label for="'. $forId .'" class="gspb-radio__style">';
                        $html .= $option['label'];
                        $html .= '<input type="radio" id="' . $forId . '" name="' . $uniqueName . '" value="' . $option['slug'] . '" data-filter="' . $filter_type . '" data-slug="' . $key . '" data-key="gspbfilter" data-val="' . $option['value'] . '" data-connection="' . $filterId . '" data-waschecked="" data-operator="' . $filter_operator . '" />';
                        $html .= '<div class="gspb-radio__checkmark"></div>';
                        $html .= '<span class="gs-filters-counter"></span>';
                        $html .= '</label>';
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Select Style
     */
    public function gspbSelect( $options = array(), $filter_type = '', $key = '', $filterId = '', $blockId = '', $attributes_arr = array(), $filter_operator = '' ) {
        if( empty( $options ) ) {
            return false;
        }

        $html = '';

        $select_label = isset($attributes_arr['labels']['select']) ? $attributes_arr['labels']['select'] : __('Select', 'greenshiftquery');

        $forId = $blockId . '-select';
        $html .= '<div data-type="' . $filter_type . '" data-query="' . $key . '" ' .$attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps($attributes_arr['animation'], $attributes_arr['interactionLayers']) . ' id="'. $blockId .'">';
            $html .= '<div class="gspb-filter-item gspb-select__item gspb-item">';
            $html .= '<select id="' . $forId . '" class="gspb-select" data-filter="' . $filter_type . '" data-slug="' . $key . '" data-key="gspbfilter" data-connection="' . $filterId . '">';
            $html .= '<option value="">' . esc_attr($select_label) . '</option>';
            foreach ($options as $option) {
                $optionClass = isset($option['class']) ? $option['class'] : '';
                $html .= '<option class="' . $optionClass . '" data-counter-prefix="(" data-counter-suffix=")" value="' . $option['slug'] . '" data-val="' . $option['value'] . '" data-operator="' . $filter_operator . '" >' . $option['label'] . '</option>';
            }
            $html .= '</select>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Tags Style
     */
    public function gspbTags( $options = array(), $filter_type = '', $key = '', $filterId = '', $blockId = '', $attributes_arr = array(),$filter_operator = '' ) {
        if ( empty( $options ) ) {
            return false;
        }

        $html = '';
        $html .= '<div data-type="' . $filter_type . '" data-query="' . $key . '" ' .$attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps($attributes_arr['animation'], $attributes_arr['interactionLayers']) . ' id="'. $blockId .'">';
            $html .= '<div class="gspb-filter-item">';
                $html .= '<div class="gspb-tags__items">';
                    foreach ( $options as $option ) {
                        $forId = $blockId . '-' . $option['value'];
                        $forName = $blockId . '-' .$option['slug'];
                        $optionClass = isset($option['class']) ? $option['class'] : '';
                        $html .= '<div class="gspb-tags__item  gspb-item ' . $optionClass . '">';
                        $html .= '<label class="gspb-filter-tag">';
                        $html .= '<input type="checkbox" id="' . $forId . '" name="' .$forName .'" value="'. $option['slug'] .'" data-filter="' . $filter_type . '"  data-val="' . $option['value'] . '" data-slug="' . $key . '" data-key="gspbfilter" data-connection="' . $filterId . '"  data-operator="' . $filter_operator . '"  />';
                        $html .= '<div class="gspb-checkbox__label">' .  $option['label'];
                        $html .= '<span class="gs-filters-counter"></span>';
                        $html .= '</div>';
                        $html .= '</label>';
                        $html .= '</div>';
                    }
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';


        return $html;
    }

    /**
     * Range Slider
     */
    public function gspbRangeSlider( $filter_type = '', $minValue = '', $maxValue = '', $stepValue = '', $metaKey = '', $filterId = '', $blockId = '', $attributes_arr = array() ) {
        $html = '';
        $uniqueId = $blockId . '-' . $metaKey;

        $html .= '<div data-minmax="' . $minValue . '|'. $maxValue .'" data-range="range" data-type="' . $filter_type . '" data-query="' . $metaKey . '" ' .$attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps($attributes_arr['animation'], $attributes_arr['interactionLayers']) . ' id="'. $blockId .'">';
        $html .= '<div class="gspb-range__slider gspb-item" style="--gspb-range-slider-low-val: 0; --gspb-range-slider-high-val: 100">';

            $html .= '<input type="range" data-minActive="true" data-type="range__input" min="' . $minValue . '" max="' . $maxValue . '" step="' . $stepValue . '" value="' . $minValue . '" class="gspb-range__slider-input ' . $metaKey . '_min" id="'. $uniqueId .'" data-filter="meta_query"  data-slug="' . $metaKey . '" data-key="gspbfilter" data-connection="' . $filterId . '" data-min="' . $minValue . '" data-max="' . $maxValue . '" data-step="' . $stepValue . '" />';
            $html .= '<input type="range" data-maxActive="true" data-type="range__input" min="' . $minValue . '" max="' . $maxValue . '" step="' . $stepValue . '" value="' . $maxValue . '" class="gspb-range__slider-input ' . $metaKey . '_max" id="'. $uniqueId .'" data-filter="meta_query"  data-slug="' . $metaKey . '" data-key="gspbfilter" data-connection="' . $filterId . '" data-min="' . $minValue . '" data-max="' . $maxValue . '" data-step="' . $stepValue . '"  />';
            $html .= '<div class="gspb-range__slider-display" data-low="' . $minValue . '" data-high="' . $maxValue . '">';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Search Input
     */
    public function gspbSearchInput( $filterId = '', $blockId = '', $attributes_arr = array(), $searchPlaceholderText = '' ) {
        $html = '';

        $forId = $blockId . '-search';
        $forName = $blockId . '-search';
        $html .= '<div ' .$attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps($attributes_arr['animation'], $attributes_arr['interactionLayers']) . ' id="'. $blockId .'">';
        $html .= '<div class="gspb-filter-item gspb-search-item">';
        $html .= '<input type="text" id="' . esc_attr( $forId ) . '" class="gspb-search" data-filter="search" name="' . esc_attr( $forName ) . '" placeholder="' . esc_attr( $searchPlaceholderText ) . '" data-key="gspbfilter" data-connection="' . esc_attr( $filterId ) . '" />'; // New Code: Search input box placeholder text
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * New Code: Rating Style
     */
    public function gspbRating( $options = array(), $filter_type = '', $key = '', $filterId = '', $blockId = '', $attributes_arr = array(), $filter_operator = '' ) {
        $html = '';

        $html .= '<div data-type="' . esc_attr( $filter_type ) . '" data-query="' . esc_attr( $key ) . '" ' .$attributes_arr['wrapper_attributes'] . gspb_AnimationRenderProps( $attributes_arr['animation'], $attributes_arr['interactionLayers'] ) . ' id="'. esc_attr( $blockId ) .'">';
            $html .= '<div class="gspb-filter-item gspb-checkbox-box">';

            if ( ! empty( $options ) ) {
                foreach ( $options as $option ) {
                    if ( isset( $option['postCount'] ) && 0 !== $option['postCount'] ) {
                        $forId       = $blockId . '-' . $option['value'];
                        $forName     = $blockId . '-' .$option['slug'];
                        $optionClass = isset( $option['class'] ) ? $option['class'] : '';

                        $html .= '<div class="gspb-checkbox__item gspb-item ' . esc_attr( $optionClass ) . '">';
                        $html .= '  <label for="' . esc_attr( $forId ) . '" class="gspb-checkbox gspb-checkbox__style">';
                        $html .= '      <input type="checkbox" id="' . esc_attr( $forId ) . '" name="' . esc_attr( $forName ) .'" value="'. esc_attr( $option['slug']) .'" data-filter="' . esc_attr( $filter_type ) . '" data-slug="' . esc_attr( $key ). '" data-key="gspbfilter" data-val="' . esc_attr( $option['value'] ) . '" data-connection="' . esc_attr( $filterId ) . '" data-operator="' . esc_attr( $filter_operator ) . '" />';
                        $html .= '      <div class="gspb-checkbox__checkmark"></div>';
                        $html .= '      <div class="gspb-checkbox__body">';

                        for ( $i = 0; $i < 5; $i++ ) {
                            $html .= '      <span class="gspb-star ' . ( $i < $option['value'] ? 'gspb-star-filled' : 'gspb-star-empty' ) . '">&#9733;</span>';
                        }

                        $html .= '      </div>';
                        $html .= '  </label>';
                        $html .= '  <span class="gs-filters-counter"></span>';
                        $html .= '</div>';
                    }
                }
            }

            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

}

new FilterBlock();
