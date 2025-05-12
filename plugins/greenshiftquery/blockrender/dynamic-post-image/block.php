<?php


namespace greenshiftquery\Blocks;

defined('ABSPATH') or exit;


class DynamicPostImage
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
		'link_enable'       => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'image_size'  => array(
			'type'    => 'string',
			'default' => 'large',
		),
		'additional'  => array(
			'type'    => 'string',
			'default' => 'no',
		),
		'post_type' => array(
			'type' => 'string',
			'default' => 'h2'
		),
		'disablelazy'       => array(
			'type'    => 'boolean',
			'default' => false,
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
			'default' => 'featured'
		),
		'imageid' => array(
			'type' => 'number',
			'default' => 0
		),
		'imageurl' => array(
			'type' => 'string',
			'default' => ''
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
		'clipPathBound' => array(
			'type' => 'boolean',
			'default' => false
		),
		'clipPathCode' => array(
			'type' => 'string',
			'default' => ''
		),
	);

	public function addClipPathToSVG($svgString, $clipPathBound = false, $id = null) {
		$svgDoc = new \DOMDocument();
		$sanitize = $svgDoc->loadXML($svgString, LIBXML_NONET | LIBXML_NOERROR);

		if ($sanitize === false) {
			// Handle the error when loading the SVG string fails
			return $svgString; // Or any other appropriate error handling
		}
	
		$xPath = new \DOMXPath($svgDoc);
	
		// Remove <script> tags
		$scriptNodes = $xPath->query('//script');
		foreach ($scriptNodes as $scriptNode) {
			$scriptNode->parentNode->removeChild($scriptNode);
		}
	
		// Remove remote references
		$remoteReferenceNodes = $xPath->query('//*[starts-with(@href, "http://") or starts-with(@href, "https://") or starts-with(@src, "http://") or starts-with(@src, "https://")]');
		foreach ($remoteReferenceNodes as $remoteReferenceNode) {
			$remoteReferenceNode->parentNode->removeChild($remoteReferenceNode);
		}
	
		// Remove event handlers
		$eventHandlerNodes = $xPath->query('//*[starts-with(@on, "on")]');
		foreach ($eventHandlerNodes as $eventHandlerNode) {
			$eventHandlerNode->removeAttribute('on');
		}
	
		$success = $svgDoc->loadXML($svgDoc->saveXML(), LIBXML_NONET | LIBXML_NOERROR);

		if ($success === false) {
			// Handle the error when loading the SVG string fails
			return $svgString; // Or any other appropriate error handling
		}
	
		$clipPathId = 'clip' . $id;
		$clipPathElement = $svgDoc->createElementNS('http://www.w3.org/2000/svg', 'clipPath');
		$clipPathElement->setAttribute('id', $clipPathId);
	
		if ($clipPathBound) {
			$clipPathElement->setAttribute('clipPathUnits', 'objectBoundingBox');
		}
	
		// Move all children of the SVG root to the clipPathElement
		$svgRoot = $svgDoc->documentElement;
		while ($svgRoot->firstChild) {
			$childNode = $svgRoot->firstChild;
			$svgRoot->removeChild($childNode);
			$clipPathElement->appendChild($childNode);
		}
	
		$svgRoot->appendChild($clipPathElement);
	
		return $svgDoc->saveXML();
	}
	

	public function render_block($settings = array(), $inner_content = '')
	{
		extract($settings);

		$additional_classes = $additional !== 'no' ? $additional : '';
		if ($disablelazy) $additional_classes .= ' no-lazyload';
		$link = '';
		$linkargs = '';
		if($linkNewWindow){
			$linkargs .= ' target="_blank"';
		}
		if($linkNoFollow || $linkSponsored){
			$linkargs .= ' rel="'.($linkNoFollow ? 'nofollow' : '').''.($linkSponsored ? ' sponsored' : '').'"';
		}

		$loading = $disablelazy ? "eager" : "lazy";
		$fetch = $disablelazy ? ' fetchpriority="high"' : "";
		$imageargs = array(
			'class' => $additional_classes,
			'loading' => $loading
		);
		if($disablelazy){
			$imageargs['fetchpriority'] = "high";
		}

		if (!empty($repeaterArray) && !empty($repeaterField)) {
			$image = GSPB_get_value_from_array_field($repeaterField, $repeaterArray);
			if (is_array($image)) {
				$url = $alt = '';
				if (!empty($image['sizes'][$image_size])) {
					$url = $image['sizes'][$image_size];
				} else if (!empty($image['url'])) {
					$url = $image['url'];
				}
				if (!empty($image['alt'])) {
					$alt = $image['alt'];
				}
				$image = '<img src="' . $url . '" loading="'.$loading.'" alt="' . $alt . '" class="' . $additional_classes . '"'.$fetch.'>';
			} else if (is_numeric($image)) {
				$image = wp_get_attachment_image($image, $image_size, false, $imageargs);
			} else {
				$image = '<img src="' . $image . '" loading="'.$loading.'" alt="" class="' . $additional_classes . '"'.$fetch.'>';
			}
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
				$result = GSPB_get_custom_field_value($postId, $dynamicField, 'no');
				if (is_numeric($result)) {
					$result = wp_get_attachment_image($result, $image_size, false, $imageargs);
					$image = $result;
				}else{
					$image = !empty($result) ? '<img loading="'.$loading.'" src="'.esc_url($result).'" alt=""'.$fetch.' />' : '';
				}
			}else{
				$image = get_the_post_thumbnail($postId, $image_size, $imageargs);
			}
			if($link_enable && $linkType == 'field' && !empty($linkTypeField)){
				$link = GSPB_get_custom_field_value($postId, $linkTypeField, 'no');
			}
		}

		$fallbackimage = '<svg width="500" height="500" class="gspb_svg_placeholder" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect width="500" height="500" fill="transparent"/>
    <g clip-path="url(#clip0_1521_865)">
    <path d="M196 262.573L226.452 231.96L285.38 291" stroke="black"/>
    <path d="M271.8 230.867C271.8 236.63 267.142 241.3 261.4 241.3C255.658 241.3 251 236.63 251 230.867C251 225.103 255.658 220.433 261.4 220.433C267.142 220.433 271.8 225.103 271.8 230.867Z" stroke="black"/>
    <path d="M259.22 264.76L282.11 241.8L305 265.853" stroke="black"/>
    </g>
    <rect x="196.5" y="209.5" width="108" height="81" stroke="black"/>
    <defs>
    <clipPath id="clip0_1521_865">
    <rect x="196" y="209" width="109" height="82" fill="white"/>
    </clipPath>
    </defs>
    </svg>';

		if($imageid > 0){
			$loading = $disablelazy ? "eager" : "lazy";
			$fallbackimage = wp_get_attachment_image($imageid, $image_size, false, ['class' => $additional_classes, 'loading' => $loading]);
		} else if(!empty($imageurl)){
			$loading = $disablelazy ? "eager" : "lazy";
			$fallbackimage = '<img loading="'.$loading.'" src="'.esc_url($imageurl).'" alt="" class="' . $additional_classes . '">';
		}

		$image = !empty($image) ? $image : $fallbackimage;

		$blockId = 'gspb_id-' . esc_attr($id);

		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb-dynamic-post-image ',
				...$data_attributes
			)
		);

		$out = '<div '.$wrapper_attributes . gspb_AnimationRenderProps($animation, $interactionLayers) . '>';
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
				$out .= '<a href="' . get_permalink($postId) . '" title="'.get_the_title($postId).'"'.$linkargs.'>';
			}
		}
		$out .= $image;
		if ($link_enable) $out .= '</a>';
		if(!empty($clipPathCode)){
			$out .= $this->addClipPathToSVG($clipPathCode, $clipPathBound, $id);
		}
		$out .= '</div>';
		return $out;
	}
}

new DynamicPostImage;
