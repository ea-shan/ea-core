<?php

namespace greenshiftaddon\Blocks;

defined('ABSPATH') or exit;


class DynamicGallery
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
		'postId'       => array(
			'type'    => 'number',
			'default' => 0,
		),
		'post_type' => array(
			'type' => 'string',
			'default' => 'post'
		),
		'dynamicField' => array(
			'type' => 'string',
			'default' => ''
		),
		'lightbox' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'field_format' => array(
			'type' => 'string',
			'default' => 'array_ids',
		),
		'isSlider' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'limit' => array(
			'type' => 'number',
			'default' => ''
		),
		'enableThumbnail' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'repeaterField' => array(
			'type' => 'string',
			'default' => ''
		),
		'enableStatic' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'images' => array(
			'type' => 'array',
			'default' => array()
		),
		'image_size' => array(
			'type' => 'string',
			'default' => 'full'
		),
		'disablelazyload' => array(
			'type' => 'boolean',
			'default' => false,
		),
		'disableAttachments' => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
		)
	);

	public function isVideoUrl($url)
	{
		// Supported video extensions
		$supportedExtensions = array('mp4', 'avi', 'mov');

		// Get the file extension from the URL
		$urlExtension = strtolower(pathinfo($url, PATHINFO_EXTENSION));

		// Check if the extension is in the list of supported extensions
		return in_array($urlExtension, $supportedExtensions);
	}

	public function render_block($settings = array(), $inner_content = '')
	{
		extract($settings);
		//print_r($settings);
		$blockId = 'gspb_id-' . esc_attr($id);
		$data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb-dynamic-acf-gallery',
				...$data_attributes
			)
		);
		$out = '<div ' .$wrapper_attributes . gspb_AnimationRenderProps($animation, $interactionLayers) . '>';

		$fileGallery = $variationGallery = false;

		if (!$enableStatic) {
			if ($sourceType == 'latest_item') {
				global $post;
				if (is_object($post)) {
					$postId = $post->ID;
				}
			} else {
				$postId = (isset($postId) && $postId > 0) ? (int)$postId : 0;
				if ($postId == 0) {
					$args = array(
						'post_type' => $post_type,
						'posts_per_page'  => 1,
						'fields' => 'ids',
						'post_status' => 'publish'
					);
					$latest_cpt = get_posts($args);
					$postId = $latest_cpt[0];
				}
			}
			if(!isset($postId) || empty($postId)){
				return '';
			}

			if (empty($dynamicField)) {
				if (!empty($repeaterArray) && !empty($repeaterField)) {
					$result = [];
					$galleryIdArray = GSPB_get_value_from_array_field($repeaterField, $repeaterArray);
					if (!empty($galleryIdArray)) {
						if (!is_array($galleryIdArray)) {
							$galleryIdArray = wp_parse_list($galleryIdArray);
						}
						if (is_array($galleryIdArray)) {
							foreach ($galleryIdArray as $image_id) {
								if (is_array($image_id)) {
									$imgsrc = $image_id['url'];
								} else if (is_numeric($image_id)) {
									$imgsrc = wp_get_attachment_url($image_id);
								} else {
									$imgsrc = $image_id;
								}
								$result[] = $imgsrc;
							}
						}
					}
					if ($enableThumbnail) {
						$im = get_the_post_thumbnail_url($postId, 'full');
						if ($im) {
							array_unshift($result, get_the_post_thumbnail_url($postId, 'full'));
						}
					}
				} else {
					$out = '<div>Please select gallery field.</div>';
				}
			}

			if (!empty($dynamicField)) {

				$result = [];
				$idarray = [];
				$field = $dynamicField;
				if ($field_format == 'array_ids') {
					$galleryIdArray = get_post_meta($postId, $field, true);
					if (!is_array($galleryIdArray) && strpos($galleryIdArray, ',') !== false) $galleryIdArray = wp_parse_list($galleryIdArray);
					if (!is_array($galleryIdArray)) $galleryIdArray = get_post_meta($postId, $field, false);
					if (!empty($galleryIdArray)) {
						foreach ($galleryIdArray as $image_id) {
							$imgsrc = wp_get_attachment_url($image_id);
							$result[] = $imgsrc;
							$idarray[] = $image_id;
						}
					}
				} else if ($field_format == 'array_urls') {
					$galleryData = get_post_meta($postId, $field, true);
					if (is_array($galleryData) && !empty($galleryData)) {
						$result = $galleryData;
					}else if(!is_array($galleryData) && !empty($galleryData)){
						$result = wp_parse_list($galleryData);
					}
				} else if ($field_format == 'acf_gallery') {
					if (function_exists('get_field')) {
						$galleryIdArray = get_field($field, $postId);
						if (!empty($galleryIdArray)) {
							foreach ($galleryIdArray as $image_id) {
								if (is_array($image_id)) {
									$imgsrc = $image_id['url'];
								} else if (is_numeric($image_id)) {
									$imgsrc = wp_get_attachment_url($image_id);
									$idarray[] = $image_id;
								} else {
									$imgsrc = $image_id;
								}
								$result[] = $imgsrc;
							}
						}
					}
				} else if ($field_format == 'post_images') {
					$galleryIdArray = get_attached_media('image', $postId);
					if (!empty($galleryIdArray)) {
						foreach ($galleryIdArray as $image_id) {
							$result[] = $image_id['guid'];
						}
					}
				}
				if ($enableThumbnail) {
					$im = get_the_post_thumbnail_url($postId, 'full');
					if ($im) {
						array_unshift($result, get_the_post_thumbnail_url($postId, 'full'));
						if (!empty($idarray)) {
							$imid = get_post_thumbnail_id($postId);
							array_unshift($idarray, $imid);
						}
					}
				}
				$fileGallery = get_post_meta($postId, 'greenshiftwoo_extended_gallery', true);
				$threegalleryID = '';
				$threegallery = get_post_meta($postId, 'greenshiftwoo360_image_gallery', true);
				$threegalleryArray = (!empty($threegallery)) ? explode(',', $threegallery) : '';
				if (!empty($threegalleryArray)) {
					$threegalleryID = $threegalleryArray[0];
				}
			}
		} else {
			if (empty($images)) {
				$out = '<div>Please select gallery field.</div>';
			} else {
				$result = [];
				$idarray = [];
				foreach ($images as $image) {
					$imgsrc = $image['url'];
					$result[] = $imgsrc;
					$idarray[] = $image['id'];
				}
			}
		}


		$lightboxClass = $lightbox ? ' gspb_gallery_lightbox' : '';
		$isSliderClass = $isSlider ? ' swiper' : '';
		if (!empty($result) || (!empty($fileGallery[0]['image']) && !$disableAttachments) || (!empty($threegalleryID) && !$disableAttachments)) {
			$out .= '<div class="gspb_gallery_grid' . $lightboxClass . $isSliderClass . '">';
			if ($isSlider) $out .= '<div class="swiper-wrapper">';
			if ($limit && is_array($result)) {
				$result = array_slice($result, 0, $limit);
			}
			foreach ($result as $key => $imagesrc) {
				$image_alt = '';
				$image_caption = '';
				$video = '';
				if (!empty($idarray[$key])) {
					$image_alt = get_post_meta($idarray[$key], '_wp_attachment_image_alt', TRUE);
					$video = get_post_meta($idarray[$key], 'gs_video_field', TRUE);
					$image_caption = wp_get_attachment_caption($idarray[$key]);
					if(!$image_caption){
						$image_caption = $image_alt;
					}
				};
				if ($isSlider) $out .= '<div class="swiper-slide">';
				if ($lightbox) {
					if ($video && filter_var($video, FILTER_VALIDATE_URL)) {
						$out .= gs_video_thumbnail_html($video, $idarray[$key], $image_alt, '60', 'full');
					} else {
						$out .= '<a href="' . $imagesrc . '" class="imagelink" title="'.esc_attr($image_caption).'">';
						if ($this->isVideoUrl($imagesrc)) {
							$out .= '<video class="gs-video-element-gallery" loading="lazy" src="' . esc_url($imagesrc) . '" autoplay playsinline loop muted></video>';
						} else {
							if (!empty($idarray[$key])) {
								$imageargs = array(
									'loading' => 'lazy',
								);
								if ($disablelazyload) $imageargs['loading'] = 'eager';
								$out .= wp_get_attachment_image($idarray[$key], $image_size, false, $imageargs);
							} else {
								$loading = $disablelazyload ? 'eager' : 'lazy';
								$out .= '<img loading="' . $loading . '" src="' . esc_url($imagesrc) . '" alt="' . esc_attr($image_alt) . '" />';
							}
						}
						$out .= '</a>';
					}
				} else {
					if ($video && filter_var($video, FILTER_VALIDATE_URL)) {
						$out .= gs_video_thumbnail_html($video, $idarray[$key], $image_alt, '60', 'full');
					} else {
						if ($this->isVideoUrl($imagesrc)) {
							$out .= '<video class="gs-video-element-gallery" loading="lazy" src="' . esc_url($imagesrc) . '" autoplay playsinline loop muted></video>';
						} else {
							if (!empty($idarray[$key])) {
								$imageargs = array(
									'loading' => 'lazy',
								);
								if ($disablelazyload) $imageargs['loading'] = 'eager';
								$out .= wp_get_attachment_image($idarray[$key], $image_size, false, $imageargs);
							} else {
								$loading = $disablelazyload ? 'eager' : 'lazy';
								$out .= '<img loading="' . $loading . '" src="' . esc_url($imagesrc) . '" alt="' . esc_attr($image_alt) . '" />';
							}
						}
					}
				}
				if ($isSlider) {
					$out .= '</div>';
				}
			}
			if (!empty($fileGallery[0]['image']) && !$disableAttachments){
				foreach ($fileGallery as $file){
					$fileimageID = !empty($file['image']) ? $file['image'] : '';
					$fileURL = !empty($file['file']) ? $file['file'] : '';
					$file_alt = $fileimageID ? get_post_meta($fileimageID, '_wp_attachment_image_alt', TRUE) : '';
					if (empty($file_alt)) {
						$file_alt = get_the_title($postId);
					}
					if ($isSlider){
						$swiperno = ($fileURL && (strpos($fileURL, '.gltf') != false || strpos($fileURL, '.glb') != false || strpos($fileURL, '.splinecode') != false)) ? 'swiper-no-swiping' : '';
						$out .= '<div class="swiper-slide'.$swiperno.'">';
					}
					$out .= gs_video_thumbnail_html($fileURL, $fileimageID, $file_alt, '60', 'woocommerce_single');
					if ($isSlider) {
						$out .= '</div>';
					}
				}
			}
			if (!empty($threegalleryID) && !$disableAttachments){
				$out .= '<div class="swiper-slide swiper-no-swiping">
					'.do_blocks('<!-- wp:greenshift-blocks/threesixty {"id":"gsbp-6fa4b380-1fc5","inlineCssStyles":".gspb_id-gsbp-6fa4b380-1fc5 img{object-fit:cover;display:block;}.gspb_id-gsbp-6fa4b380-1fc5 .gspb-threesixty-lightbox{position:absolute;width:30px;height:30px;left:15px;bottom:15px;background-color:white;border-radius:50%;display:flex;justify-content:center;align-items:center;cursor:pointer;z-index:2;}.gspb_id-gsbp-6fa4b380-1fc5 .gspb-threesixty-lightbox svg{width:15px;}.gspb_id-gsbp-6fa4b380-1fc5 .gspb-threesixty-gallery_placeholder{position:absolute;top:0;left:0;right:0;bottom:0;z-index:1;width:100%;height:100%;transition:opacity 0.3s ease-in-out;}.gspb_id-gsbp-6fa4b380-1fc5:hover .gspb-threesixty-gallery_placeholder{opacity:0;}.gspb_id-gsbp-6fa4b380-1fc5 .gspb-threesixty-gallery_grid{position:relative;}.gspb_id-gsbp-6fa4b380-1fc5:hover{cursor:grab;}.gspb_id-gsbp-6fa4b380-1fc5 .gspb-threesixty-gallery_placeholder span{position:absolute;width:70px;height:70px;left:50%;top:50%;margin:-35px 0 0 -35px;background-color:white;border-radius:50%;display:flex;justify-content:center;align-items:center;}.gspb_id-gsbp-6fa4b380-1fc5 .gspb-threesixty-gallery_placeholder svg{width:35px;}.gspb_id-gsbp-6fa4b380-1fc5 img{width:100%;max-width:100%;}.gspb_id-gsbp-6fa4b380-1fc5 img{height:auto;}","dynamicField":"greenshiftwoo360_image_gallery","post_type":"product","disablelazyload":true,"autoplay":true,"lightbox":true} /-->').'
				</div>';
			}
			if ($isSlider) {
				$out .= '</div>';
			}
			$out .= '</div>';
		}else{
			return '';
		}

		$out .= '</div>';
		return $out;
	}
}

new DynamicGallery;
