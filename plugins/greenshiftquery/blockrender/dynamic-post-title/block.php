<?php

namespace greenshiftquery\Blocks;

defined('ABSPATH') or exit;


class DynamicPostTitle
{

	public function __construct()
	{
		add_action('init', array($this, 'init_handler'));
	}

	public function init_handler()
	{
		register_block_type(
			__DIR__,
			array(
				'render_callback' => array($this, 'render_block'),
				'attributes'      => $this->attributes
			)
		);
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
		'sourceType'       => array(
			'type'    => 'string',
			'default' => 'latest_item',
		),
		'label'       => array(
			'type'    => 'string',
			'default' => '',
		),
		'postfix'       => array(
			'type'    => 'string',
			'default' => '',
		),
		'postId'       => array(
			'type'    => 'number',
			'default' => 0,
		),
		'headingTag' => array(
			'type' => 'string',
			'default' => 'h2'
		),
		'post_type' => array(
			'type' => 'string',
			'default' => 'h2'
		),
		'link_enable'       => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'repeaterField' => array(
			'type' => 'string',
			'default' => ''
		),
		'dynamicField' => array(
			'type' => 'string',
			'default' => ''
		),
		'dynamicType' => array(
			'type' => 'string',
			'default' => 'title'
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

	public function render_block($settings = array(), $inner_content = '')
	{
		extract($settings);
		$link = '';
		$linkargs = '';
		if($linkNewWindow){
			$linkargs .= ' target="_blank"';
		}
		if($linkNoFollow || $linkSponsored){
			$linkargs .= ' rel="'.($linkNoFollow ? 'nofollow' : '').''.($linkSponsored ? ' sponsored' : '').'"';
		}
		if (!empty($repeaterArray) && !empty($repeaterField)) {
			$title = GSPB_get_value_from_array_field($repeaterField, $repeaterArray);
			if($link_enable && $linkType == 'repeater' && !empty($linkTypeField)){
				$link = GSPB_get_value_from_array_field($linkTypeField, $repeaterArray);
			}
		} else {
			if ($sourceType == 'latest_item') {
				global $post;
				if (is_object($post)) {
					$postId = $post->ID;
				}
			} else {
				$postId = (isset($postId) && $postId > 0) ? (int)$postId : 0;
			}

			//$_post = gspb_get_post_object_by_id($postId, $post_type);
			//if (!$_post) return '';
			if($dynamicField && $dynamicType == 'custom'){
				$title = GSPB_get_custom_field_value($postId, $dynamicField, 'no');
			}else{
				$title = get_the_title($postId);
			}
			if($link_enable && $linkType == 'field' && !empty($linkTypeField)){
				$link = GSPB_get_custom_field_value($postId, $linkTypeField, 'no');
			}
		}

		$blockId = 'gspb_id-' . esc_attr($id);
		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb-dynamic-post-title',
				...$data_attributes
			)
		);
		
		$out = '<div ' .$wrapper_attributes . gspb_AnimationRenderProps($animation, $interactionLayers) . '>';
		$out .= '<' . $headingTag . ' class="gspb-dynamic-title-element">';
		if ($link_enable) {
			if($link){
				$out .= '<a href="' . esc_url($link) . '"'.$linkargs.'>';
			}else if (!empty($repeaterArray) && !empty($repeaterField)) {
				if (!empty($repeaterArray['link'])) {
					$out .= '<a href="' . esc_url($repeaterArray['link']) . '"'.$linkargs.'>';
				} else if (!empty($repeaterArray['link_to_post'])) {
					$out .= '<a href="' . esc_url($repeaterArray['link_to_post']) . '"'.$linkargs.'>';
				} else if (!empty($repeaterArray['ID'])) {
					$out .= '<a href="' . get_permalink($repeaterArray['ID']) . '"'.$linkargs.'>';
				}
			} else {
				$out .= '<a href="' . get_permalink($postId) . '"'.$linkargs.'>';
			}
		}
		$out .= do_shortcode($title);
		if ($link_enable) $out .= '</a>';
		$out .= '</' . $headingTag . '>';
		$out .= '</div>';
		return $out;
	}
}

new DynamicPostTitle;
