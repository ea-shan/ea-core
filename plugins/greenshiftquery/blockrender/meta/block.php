<?php

namespace Greenshift\Blocks;
defined('ABSPATH') OR exit;

class MetaBox{

	public function __construct(){
		add_action('init', array( $this, 'init_handler' ));
	}

	public function init_handler(){
		register_block_type(__DIR__, array(
			'attributes' => $this->attributes,
			'render_callback' => array( $this, 'render_block' ),
		));
	}

	public $attributes = array(
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
		'postId'      => array(
			'type'    => 'number',
		),
		'field' => array(
			'type'    => 'string',
			'default' => '',
		),
		'subfield' => array(
			'type'    => 'string',
			'default' => '',
		),
		'subsubfield' => array(
			'type'    => 'string',
			'default' => '',
		),
		'postprocessor' => array(
			'type'    => 'string',
			'default' => '',
		),
		'acfrepeattype' => array(
			'type'    => 'string',
			'default' => '',
		),
		'prefix' => array(
			'type'    => 'string',
			'default' => '',
		),
		'postfix' => array(
			'type'    => 'string',
			'default' => '',
		),
		'replaceLabel' => array(
			'type'    => 'string',
			'default' => '',
		),
		'type' => array(
			'type'    => 'string',
			'default' => 'custom',
		),
		'loading' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'showtoggle' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'show_empty' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'labelblock' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'selectedPostId' => array(
			'type'    => 'number',
		),
		'post_type' => array(
			'type'    => 'string',
			'default' =>''
		),
		'repeaternumber' => array(
			'type'    => 'number',
			'default'=> 0
		),
		'source' => array(
			'type'    => 'string',
			'default'=> 'latest_item'
		),
		'enableIcon' => array(
			'type'    => 'boolean',
			'default' => false
		),
		'iconBox_icon'=> array(
			'type' => 'object',
			'default' => []
		),
		'repeaterField' => array(
			'type' => 'string',
			'default' => ''
		),
		'link_enable'       => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'linkType' => array(
			'type' => 'string',
			'default' => ''
		),
		'linkTypeField' => array(
			'type' => 'string',
			'default' => ''
		),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
		),
		'textTag' => array(
			'type' => 'string',
			'default' => ''
		),
		'linkNewWindow'=> array(
			'type' => 'boolean',
			'default' => false
		),
		'linkNoFollow' => array(
			'type' => 'boolean',
			'default' => false
		),
		'linkSponsored' => array(
			'type' => 'boolean',
			'default' => false
		),

	);

	public function render_block($settings = array(), $inner_content = ''){
		extract($settings);
		$postId = 0;
		$link = '';
		$linkargs = '';
		if($linkNewWindow){
			$linkargs .= ' target="_blank"';
		}
		if($linkNoFollow || $linkSponsored){
			$linkargs .= ' rel="'.($linkNoFollow ? 'nofollow' : '').''.($linkSponsored ? ' sponsored' : '').'"';
		}
		if(isset($selectedPostId) && $source == 'definite_item' && $selectedPostId){
			$postId = (int)$selectedPostId;
		}else{
			if(empty($postId)){
				global $post;
				if (is_object($post)) {
					$postId = $post->ID;
				}
			}
		}
		if($link_enable){
			if (!empty($repeaterArray) && !empty($linkTypeField) && $linkType == 'repeater') {
				$link = GSPB_get_value_from_array_field($linkTypeField, $repeaterArray);
			}else if($linkType == 'field' && !empty($linkTypeField)){
				$link = GSPB_get_custom_field_value($postId, $linkTypeField, 'no');
			}else if($linkType == 'authormeta' && !empty($linkTypeField)){
				$link = GSPB_get_custom_field_value($postId, $linkTypeField, 'no', 'authormeta');
			}else if($linkType == 'currentusermeta' && !empty($linkTypeField)){
				$link = GSPB_get_custom_field_value($postId, $linkTypeField, 'no', 'currentusermeta');
			}else if($linkType == 'taxonomymeta' && !empty($linkTypeField)){
				$link = GSPB_get_custom_field_value($postId, $linkTypeField, 'no', 'taxonomymeta');
			}
			$link = apply_filters('greenshiftseo_url_filter', $link);
			$link = apply_filters('rh_post_offer_url_filter', $link);
		}
		if((!isset($postId) || empty($postId)) && $type != 'archivename' && $type != 'archivedescription' && $type != 'archivecount'){
			//return '';
		}
		$blockId = 'gspb_id-' . esc_attr($id);
		$field = !empty($repeaterField) ? $repeaterField : $field;
		$repeaterArray = !empty($repeaterArray) ? $repeaterArray : [];
		$metaVal = gspb_query_get_custom_value(array('field'=>$field, 'subfield'=>$subfield,  'subsubfield'=>$subsubfield, 'post_id'=>$postId, 'type'=>$type, 'show_empty'=>$show_empty, 'prefix'=>$prefix, 'postfix'=>$postfix, 'showtoggle'=>$showtoggle, 'repeaternumber'=> $repeaternumber, 'acfrepeattype'=>$acfrepeattype, 'icon'=>$enableIcon ? $iconBox_icon : '', 'postprocessor'=> $postprocessor, 'repeaterArray'=> $repeaterArray, 'replaceLabel'=> $replaceLabel));

		if(!$metaVal){
			return '';
		}
		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb_meta',
				...$data_attributes
			)
		);
		$out = '<div ' .$wrapper_attributes . gspb_AnimationRenderProps($animation, $interactionLayers) . '>';

		if ($link_enable) {
			if($link){
				$out .= '<a href="' . esc_url($link) . '"'.$linkargs.'>';
			} else {
				$out .= '<a href="' . get_permalink($postId) . '"'.$linkargs.'>';
			}
		}
		if($textTag && $textTag != 'div'){
			$out .= '<'.esc_attr($textTag).'>';
		}
		$out .= $metaVal;
		if($textTag && $textTag != 'div'){
			$out .= '</'.esc_attr($textTag).'>';
		}
		if ($link_enable) $out .= '</a>';
		$out .= '</div>';

		return $out;
	}

}

new MetaBox;