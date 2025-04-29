<?php

/**
 * Title: Sidebar
 * Slug: express-analytics/sidebar
 * Categories: express-analytics
 */
?>

<!-- wp:group {"className":"sidebar","layout":{"type":"constrained"}} -->
<div class="wp-block-group sidebar"><!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search","buttonText":"Search"} /-->

  <!-- wp:heading -->
  <h2 class="wp-block-heading">Categories</h2>
  <!-- /wp:heading -->

  <!-- wp:categories /-->

  <!-- wp:heading -->
  <h2 class="wp-block-heading">Latest Posts</h2>
  <!-- /wp:heading -->

  <!-- wp:latest-posts /-->

  <!-- wp:heading -->
  <h2 class="wp-block-heading">Archives</h2>
  <!-- /wp:heading -->

  <!-- wp:archives /-->

  <!-- wp:heading -->
  <h2 class="wp-block-heading">Pages</h2>
  <!-- /wp:heading -->

  <!-- wp:page-list /-->

  <!-- wp:heading -->
  <h2 class="wp-block-heading">Tags</h2>
  <!-- /wp:heading -->

  <!-- wp:tag-cloud /-->

  <!-- wp:heading -->
  <h2 class="wp-block-heading">Gallery</h2>
  <!-- /wp:heading -->

  <!-- wp:gallery {"linkTo":"none"} -->
  <figure class="wp-block-gallery has-nested-images columns-default is-cropped"><!-- wp:image {"id":32,"sizeSlug":"full","linkDestination":"none","className":"is-style-default","style":{"border":{"radius":"0px","width":"0px","style":"none"}}} -->
    <figure class="wp-block-image size-full has-custom-border is-style-default"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about-team-1.jpg" alt="" class="wp-image-32" style="border-style:none;border-width:0px;border-radius:0px" /></figure>
    <!-- /wp:image -->

    <!-- wp:image {"id":30,"sizeSlug":"full","linkDestination":"none"} -->
    <figure class="wp-block-image size-full"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about-team-2.jpg" alt="" class="wp-image-30" /></figure>
    <!-- /wp:image -->

    <!-- wp:image {"id":31,"sizeSlug":"full","linkDestination":"none"} -->
    <figure class="wp-block-image size-full"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about-team-3.jpg" alt="" class="wp-image-31" /></figure>
    <!-- /wp:image -->
  </figure>
  <!-- /wp:gallery -->

  <!-- wp:calendar /-->
</div>
<!-- /wp:group -->
