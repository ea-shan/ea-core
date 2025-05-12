<?php
/**
 * greenshiftquery: Query grid with cover image
 *
 * @package Greenshiftquery
 */

return array(
	'title'         => __( 'Query Grid with Hover Container', 'greenshiftquery' ),
	'categories'    => array( 'gspb_query-query' ),
	'keywords' => array( 'query', 'grid', 'cover', 'hover' ),
	'blockTypes'    => array('greenshift-blocks/querygrid'),
	'content'       => '<!-- wp:greenshift-blocks/querygrid {"id":"gsbp-bf8dd922-842b","container_image":true,"container_image_size":"","container_overlay":{}} -->
	<!-- wp:greenshift-blocks/container {"id":"gsbp-b9af4450-c88c","flexbox":{"type":"flexbox","flexDirection":["column"],"alignItems":["center"],"justifyContent":["center"]},"background":{"color":"#ffffff"},"spacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"top":[15],"right":[15],"bottom":[15],"left":[15]},"unit":["px","px","px","px"],"locked":true}},"csstransform":{"opacity":null,"opacityHover":null,"time":"0.5","unit":["%"],"translateY":[100],"translateYHover":[0],"hoverClass":"gspbgrid_item"},"blockWidth":{"customWidth":{"value":["100%"],"unit":["px","px","px","px"]},"customHeight":{"value":["100%"],"unit":["px","px","px","px"]},"heightType":"custom","widthType":"custom","minHeight":["200px"]}} -->
	<div id="gspb_container-id-gsbp-b9af4450-c88c" class="gspb_container gspb_container-gsbp-b9af4450-c88c wp-block-greenshift-blocks-container"><!-- wp:greenshift-blocks/meta {"id":"gsbp-510a0085-42cf","typographyValue":{"textShadow":{},"colorlinks":"#2184f9","size":[14],"sizeUnit":"px","line_height":[20]},"field":"category","type":"taxonomylink","typeselect":"taxonomy"} /-->
	
	<!-- wp:greenshift-blocks/dynamic-post-title {"id":"gsbp-954ace6d-190f","spacing":{"margin":{"values":{"top":[10],"bottom":[15]},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":false}},"typography":{"textShadow":{},"sizeUnit":"rem","size":[1.2],"alignment":["center"]},"align":"center","headingTag":"h3"} /-->
	
	<!-- wp:group {"style":{"spacing":{"blockGap":"0.4rem"}},"layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","justifyContent":"left"}} -->
	<div class="wp-block-group"><!-- wp:greenshift-blocks/meta {"id":"gsbp-b375b370-eae4","typographyValue":{"textShadow":{},"size":[13],"customweight":"bold"},"typographyPostfix":{"textShadow":{},"color":"#0000003b"},"spacingValue":{"margin":{"values":{"right":[8]},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":false}},"field":"author_name","type":"author_name","typeselect":"authordata","postfix":"/"} /-->
	
	<!-- wp:greenshift-blocks/meta {"id":"gsbp-efe61988-0578","background":{"overlayOpacity":50},"typographyValue":{"textShadow":{},"size":[13]},"field":"author_name","type":"post_modified","typeselect":"postdata"} /--></div>
	<!-- /wp:group --></div>
	<!-- /wp:greenshift-blocks/container -->
	<!-- /wp:greenshift-blocks/querygrid -->',
);
