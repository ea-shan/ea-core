<?php

namespace Greenshift\Blocks;
defined('ABSPATH') OR exit;

class ThumbBox{

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
		'selectedPostId' => array(
			'type'    => 'number',
			'default' => null
		),
		'postId'      => array(
			'type'    => 'number',
		),
		'type' => array(
            'type' => 'string',
            'default'=> 'thumbs'
        ),
        'postfix' => array(
            'type'=>'string',
            'default'=> ''
        ),
        'valueBefore' => array(
            'type'=>'string',
			'default'=>''
        ),
        'tempscale' => array(
            'type'=>'boolean',
			'default'=> false
        ),
        'maxtemp' => array(
            'type'=>'number',
			'default'=> 100
        ),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
		)


	);

	public function render_block($settings = array(), $inner_content = ''){
		extract($settings);
		$out = '';
		if (isset($selectedPostId) && $selectedPostId) {
			$postId = (int)$selectedPostId;
		} else {
			if (empty($postId)) {
				global $post;
				if (is_object($post)) {
					$postId = $post->ID;
				}else{
					return '';
				}
			}
		}
		if(!isset($postId) || empty($postId)){
			return '';
		}
		$blockId = 'gspb_id-' . esc_attr($id);
		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb_thumbs',
				...$data_attributes
			)
		);
		$out .= '<div '.$wrapper_attributes.gspb_AnimationRenderProps($animation, $interactionLayers).'>
		'.gspb_query_thumb_counter(array('type'=>$type,'postfix'=>$postfix, 'post_id'=>$postId, 'maxtemp'=>$maxtemp, 'tempscale' =>$tempscale)).'
		</div>';

		return $out;
	}

}

new ThumbBox;