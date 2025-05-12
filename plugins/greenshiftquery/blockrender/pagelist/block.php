<?php


namespace Greenshift\Blocks;
defined('ABSPATH') OR exit;


class PageList{

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
        'postType' => array(
            'type' => 'string',
            'default' => 'page'
        ),
        'depth' => array(
            'type' => 'string',
            'default' => '0'
        ),
        'orderBy' => array(
            'type' => 'string',
            'default' => 'post_title'
        ),
        'order' => array(
            'type' => 'string',
            'default' => 'ASC'
        ),
        'child_of' => array(
            'type' => 'number',
            'default' => ''
        ),
        'include' => array(
            'type' => 'string',
            'default' => ''
        ),
        'exclude' => array(
            'type' => 'string',
            'default' => ''
        ),
        'link_after' => array(
            'type' => 'string',
            'default' => ''
        ),
        'link_before' => array(
            'type' => 'string',
            'default' => ''
        ),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
        ),
        'childtype' => array(
            'type' => 'string',
            'default' => 'custom'
        ),
        'enableIcon' => array(
			'type'    => 'boolean',
			'default' => false
		),
		'iconBox_icon'=> array(
			'type' => 'object',
			'default' => []
		),
	);

	public function render_block($settings = array(), $inner_content=''){
		extract($settings);

		$blockId = 'gspb_id-' . esc_attr($id);

        $data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb-pagelistbox',
				...$data_attributes
			)
		);

		$out = '<div id="'.$blockId.'" '.$wrapper_attributes.gspb_AnimationRenderProps($animation, $interactionLayers).'>';
            $beforelist = '<span class="gspb_pagelist_icon"></span>';
            if($link_before){
                $beforelist .= '<span class="link_prev_element">'.wp_kses_post($link_before).'</span>';
            }
            $out .= '<ul class="gspagelist">';
            $args = [
                'post_type' => $postType ? esc_attr($postType) : 'page',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'sort_column' => esc_attr($orderBy),
                'title_li' => '',
                'echo'=> false,
                'exclude' => $exclude ? esc_attr($exclude) : '',
                'include' => $include ? esc_attr($include) : '',
                'link_after' => $link_after ? '<span class="link_after_element">'.wp_kses_post($link_after).'</span>' : '',
                'link_before' => $beforelist,
            ];
            if($child_of && $childtype == 'custom'){
                $args['child_of'] = (int)$child_of;
            }else if($childtype == 'current'){
                $args['child_of'] = get_the_ID();
            }else if($childtype == 'sibling'){
                $args['child_of'] = wp_get_post_parent_id(get_the_ID());
            }
            if($depth){
                $args['depth'] = (int)$depth;
            }
            if(($link_after || $link_before) && !is_admin()){
                if (strpos($link_after, '{CUSTOM:') !== false || strpos($link_before, '{CUSTOM:') !== false) {
                    $html = wp_list_pages($args);
                    if (empty($html)) {
                        return '';
                    }
                    $dom = new \DOMDocument();
                    $dom->loadHTML($html);
        
                    $items = $dom->getElementsByTagName('li');
        
                    foreach ($items as $item) {

                        $itemcode = $dom->saveHTML($item);

                        $class = $item->getAttribute('class');
                        preg_match('/page-item-(\d+)/', $class, $matches);
                        
                        if (!empty($matches[1])) {
                            $id = $matches[1];

                            if($link_after){
                                preg_match('/{CUSTOM:(.*?)}/', $link_after, $matches);
                                if (!empty($matches)) {
                                    $valAfter = $matches[1];
                                    $value_After = get_post_meta((int)$id, $valAfter, true);
                                    $itemcode = str_replace('{CUSTOM:'.$valAfter.'}', $value_After, $itemcode);
                                }
                            }

                            if($link_before){
                                preg_match('/{CUSTOM:(.*?)}/', $link_before, $matches);
                                if (!empty($matches)) {
                                    $valBefore = $matches[1];
                                    $value_Before = get_post_meta((int)$id, $valBefore, true);
                                    $itemcode = str_replace('{CUSTOM:'.$valBefore.'}', $value_Before, $itemcode);
                                }
                            }
                            
                            //$item->appendChild($idElement);
                        }
                        
                        $out .= $itemcode;
                    }
                }else{
                    $out .= wp_list_pages($args);
                }
            }else{
                $out .= wp_list_pages($args);
            }
            $out .= '</ul>';
		$out .='</div>';
        if($enableIcon && !empty($iconBox_icon)){
            $out = str_replace('<span class="gspb_pagelist_icon"></span>', '<span class="gspb_pagelist_icon">'.greenshift_render_icon_module($iconBox_icon).'</span>', $out);
        }
		return $out;
	}

}

new PageList;