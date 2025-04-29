<?php

/**
 * Title: Express Analytics Chaotic Services
 * Slug: express-analytics/chaotic
 * Categories: ea-patterns
 * Keywords: services, analytics, scroll, cards
 * Block Types: core/group
 * Viewport Width: 1400
 */

$default_services = array(
  array(
    'icon' => 'analytics',
    'title' => 'Customer Satisfaction Scores'
  ),
  array(
    'icon' => 'cohort',
    'title' => 'Cohort Analysis'
  ),
  array(
    'icon' => 'conjoint',
    'title' => 'Conjoint Analysis'
  ),
  array(
    'icon' => 'voice',
    'title' => 'Voice of Customer Analysis'
  ),
  array(
    'icon' => 'lookalike',
    'title' => 'Look-alike Modeling'
  ),
  array(
    'icon' => 'recommendation',
    'title' => 'Recommendation Engine'
  ),
  array(
    'icon' => 'product',
    'title' => 'Product & Promotion Mix Analytics'
  ),
  array(
    'icon' => 'channel',
    'title' => 'Channel Marketing Analytics'
  ),
  array(
    'icon' => 'predictive',
    'title' => 'Predictive Analytics'
  ),
  array(
    'icon' => 'customer',
    'title' => 'Customer Analytics'
  )
);

// Split services into two rows
$row1_services = array_slice($default_services, 0, 5);
$row2_services = array_slice($default_services, 5);

// Get theme directory URI once
$theme_uri = get_template_directory_uri();
?>

<!-- wp:group {"align":"full","className":"chaotic-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull chaotic-section">
  <!-- wp:group {"className":"chaotic-container","layout":{"type":"constrained"}} -->
  <div class="wp-block-group chaotic-container">
    <!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontStyle":"normal","fontWeight":"600"}},"className":"chaotic-title","fontSize":"x-large"} -->
    <h2 class="wp-block-heading has-text-align-center chaotic-title has-x-large-font-size" style="font-style:normal;font-weight:600">
      <!-- wp:paragraph {"placeholder":"Enter section title..."} -->
      <p>Our Analytics Services</p>
      <!-- /wp:paragraph -->
    </h2>
    <!-- /wp:heading -->

    <!-- wp:group {"className":"chaotic-row row1","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
    <div class="wp-block-group chaotic-row row1">
      <?php foreach ($row1_services as $service) : ?>
        <!-- wp:group {"className":"chaotic-card","layout":{"type":"flex","flexWrap":"nowrap","alignItems":"center"}} -->
        <div class="wp-block-group chaotic-card">
          <!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"chaotic-icon"} -->
          <figure class="wp-block-image size-full chaotic-icon">
            <!-- wp:image {"editable":true,"placeholder":"Choose icon..."} -->
            <img src="<?php echo esc_url($theme_uri); ?>/assets/images/icons/<?php echo esc_attr($service['icon']); ?>.svg" alt="<?php echo esc_attr($service['title']); ?>" />
            <!-- /wp:image -->
          </figure>
          <!-- /wp:image -->
          <!-- wp:paragraph {"className":"chaotic-text","placeholder":"Enter service title..."} -->
          <p class="chaotic-text"><?php echo esc_html($service['title']); ?></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      <?php endforeach; ?>

      <!-- wp:button {"className":"add-card-button","style":{"border":{"radius":"20px"}}} -->
      <div class="wp-block-button add-card-button">
        <button class="wp-block-button__link" style="border-radius:20px">Add Card</button>
      </div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:group -->

    <!-- wp:group {"className":"chaotic-row row2","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
    <div class="wp-block-group chaotic-row row2">
      <?php foreach ($row2_services as $service) : ?>
        <!-- wp:group {"className":"chaotic-card","layout":{"type":"flex","flexWrap":"nowrap","alignItems":"center"}} -->
        <div class="wp-block-group chaotic-card">
          <!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"chaotic-icon"} -->
          <figure class="wp-block-image size-full chaotic-icon">
            <!-- wp:image {"editable":true,"placeholder":"Choose icon..."} -->
            <img src="<?php echo esc_url($theme_uri); ?>/assets/images/icons/<?php echo esc_attr($service['icon']); ?>.svg" alt="<?php echo esc_attr($service['title']); ?>" />
            <!-- /wp:image -->
          </figure>
          <!-- /wp:image -->
          <!-- wp:paragraph {"className":"chaotic-text","placeholder":"Enter service title..."} -->
          <p class="chaotic-text"><?php echo esc_html($service['title']); ?></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      <?php endforeach; ?>

      <!-- wp:button {"className":"add-card-button","style":{"border":{"radius":"20px"}}} -->
      <div class="wp-block-button add-card-button">
        <button class="wp-block-button__link" style="border-radius:20px">Add Card</button>
      </div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->
</div>
<!-- /wp:group -->
