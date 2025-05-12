<?php

namespace Greenshift\Blocks;

defined('ABSPATH') or exit;

class WishlistBox
{

	public function __construct()
	{
		add_action('init', array($this, 'init_handler'));
	}

	public function init_handler()
	{
		register_block_type(__DIR__, array(
			'attributes' => $this->attributes,
			'render_callback' => array($this, 'render_block'),
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
			'type'    => 'string',
			'default' => ''
		),
		'postId'      => array(
			'type'    => 'number',
		),
		'type' => array(
			'type' => 'string',
			'default' => 'button'
		),
		'icontype' => array(
			'type' => 'string',
			'default' => 'circle'
		),
		'wishlistadd' => array(
			'type' => 'string',
			'default' => 'Add to wishlist'
		),
		'wishlistadded' => array(
			'type' => 'string',
			'default' => 'Added to wishlist'
		),
		'loginpage' => array(
			'type' => 'string',
			'default' => ''
		),
		'wishlistpage' => array(
			'type' => 'string',
			'default' => ''
		),
		'noitemstext' => array(
			'type' => 'string',
			'default' => 'There is nothing in your wishlist'
		),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
		)

	);

	public function render_block($settings = array(), $inner_content = '')
	{
		extract($settings);
		$out = '';
		if ($type != 'icon') {
			if ($selectedPostId) {
				$postId = (int)$selectedPostId;
			} else {
				if (empty($postId)) {
					global $post;
					if (is_object($post)) {
						$postId = $post->ID;
					} else {
						return '';
					}
				}
			}
			if (!isset($postId) || empty($postId)) {
				return '';
			}
		} else {
			$postId = 0;
		}
		$blockId = 'gspb_id-' . esc_attr($id);
		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb_wishcounter ',
				...$data_attributes
			)
		);

		$out .= '<div ' . $wrapper_attributes . gspb_AnimationRenderProps($animation, $interactionLayers) . '>
		' . gspb_query_wishlist(array(
			'type' => $type, 'icontype' => $icontype, 'post_id' => $postId,
			'wishlistadd' => $wishlistadd, 'wishlistadded' => $wishlistadded, 'wishlistpage' => $wishlistpage, 'loginpage' => $loginpage, 'noitemstext' => $noitemstext
		)) . '
		</div>';

		return $out;
	}
}

new WishlistBox;
