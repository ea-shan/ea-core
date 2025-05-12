<?php
/**
 * greenshiftquery: Query grid with cover image
 *
 * @package Greenshiftquery
 */

return array(
	'title'         => __( 'Query Custom Grid with cover images', 'greenshiftquery' ),
	'categories'    => array( 'gspb_query-query' ),
	'keywords' => array( 'query', 'grid', 'cover' ),
	'blockTypes'    => array('greenshift-blocks/querygrid'),
	'content'       => '<!-- wp:greenshift-blocks/querygrid {"id":"gsbp-a6b05038-bcc1","show":8,"columnGrid":[4,3,2,1],"gridspan_arr":[{"title":"Condition #1","columnStart":[],"columnEnd":[],"rowStart":[],"rowEnd":[],"headingSize":["40px"],"headingLineHeight":["40px"],"columnspan":["2","2","1","1"],"rowspan":["2","2","1","1"],"itemNumber":1,"zoomFactor":null},{"title":"Condition #2","columnStart":[],"columnEnd":[],"rowStart":[],"rowEnd":[],"headingSize":[],"headingLineHeight":[],"columnspan":[],"rowspan":["2",null,"1","1"],"itemNumber":4,"zoomFactor":null}],"container_image":true,"container_overlay":{"color":"#000000","opacity":0.5}} -->
	<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"},"blockGap":"var:preset|spacing|30"},"dimensions":{"minHeight":"100%"},"color":{"text":"#fefefd"},"elements":{"link":{"color":{"text":"#fefdfd"}}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"left","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group has-text-color has-link-color" style="color:#fefefd;min-height:100%;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:greenshift-blocks/meta {"id":"gsbp-db093954-661a","typographyValue":{"textShadow":{},"colorlinks":"#ffffff","size":[14],"sizeUnit":"px","line_height":[20]},"field":"category","type":"taxonomylink","typeselect":"taxonomy"} /-->
	
	<!-- wp:greenshift-blocks/dynamic-post-title {"id":"gsbp-111f910f-0c09","spacing":{"margin":{"values":{"top":[0],"bottom":[0]},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":false}},"typography":{"textShadow":{},"sizeUnit":"rem","size":[1.45],"colorlinks":"#ffffff","decoration":"remove","line_height":["2rem"]},"clampEnable":true,"clamp":[2,null,null,null]} /-->
	
	<!-- wp:group {"style":{"spacing":{"blockGap":"0.4rem"}},"layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal","justifyContent":"left"}} -->
	<div class="wp-block-group"><!-- wp:greenshift-blocks/meta {"id":"gsbp-816bd4ce-e7c8","typographyValue":{"textShadow":{},"size":[13],"customweight":"bold"},"typographyPostfix":{"textShadow":{},"color":"#0000003b"},"spacingValue":{"margin":{"values":{"right":[8]},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{},"unit":["px","px","px","px"],"locked":false}},"field":"author_name","type":"author_name","typeselect":"authordata","postfix":"/"} /-->
	
	<!-- wp:greenshift-blocks/meta {"id":"gsbp-e266f3ee-d6de","background":{"overlayOpacity":50},"typographyValue":{"textShadow":{},"size":[13]},"field":"author_name","type":"post_modified","typeselect":"postdata"} /--></div>
	<!-- /wp:group --></div>
	<!-- /wp:group -->
	<!-- /wp:greenshift-blocks/querygrid -->',
);
