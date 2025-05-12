<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<?php if (isset($blockinstance) && is_array($blockinstance) && !isset($block_instance)) {
    $block_instance = $blockinstance;
} ?>
<?php $postid = get_the_ID(); ?>
<?php $typeclass = (!empty($block_instance['attrs']['post_type'])) ? ' type-' . $block_instance['attrs']['post_type'] : '' ?>
<?php $typeclass = apply_filters('gspbgrid_item_class', $typeclass, $postid, $block_instance); ?>
<?php $tagItem = (!empty($block_instance['attrs']['itemTag'])) ? $block_instance['attrs']['itemTag'] : 'li'; ?>
<?php $itemClasses = (!empty($block_instance['attrs']['itemClasses'])) ? $block_instance['attrs']['itemClasses'] : ''; ?>

<<?php echo $tagItem; ?> class="gspbgrid_item swiper-slide post-<?php echo (int)$postid; ?><?php echo esc_attr($typeclass); ?><?php echo $itemClasses ? ' '.esc_attr($itemClasses) : ''; ?>">
    <?php if (!empty($block_instance['attrs']['container_link'])) {
        if(!empty($block_instance['attrs']['linkType']) && $block_instance['attrs']['linkType'] == 'field' && !empty($block_instance['attrs']['linkTypeField'])) {
            $field = $block_instance['attrs']['linkTypeField'];
            $postlink = GSPB_get_custom_field_value($postid, $field);
        }else{
            $postlink = get_the_permalink($postid);
        }
        $postlink = apply_filters('gspbgrid_item_link', $postlink, $postid, $block_instance);
        $newWindow = (!empty($block_instance['attrs']['linkNewWindow'])) ? ' target="_blank"' : '';
        $linkNoFollow = (!empty($block_instance['attrs']['linkNoFollow'])) ? ' rel="nofollow"' : '';
        $linkSponsored = (!empty($block_instance['attrs']['linkSponsored'])) ? ' rel="sponsored"' : '';
        echo '<a class="gspbgrid_item_link" title="' . get_the_title($postid) . '" href="' . $postlink . '"'.$newWindow.$linkNoFollow.$linkSponsored.'></a>';
    } ?>
    <?php if (!empty($block_instance['attrs']['container_image'])) {
        ?>
            <?php $size = (!empty($block_instance['attrs']['container_image_size'])) ? $block_instance['attrs']['container_image_size'] : 'medium'; ?>
            <div class="gspbgrid_item_image_bg gspbgrid_item_image_bg_<?php echo esc_attr($block_instance['attrs']['id']);?>">
                <div class="gspb_backgroundOverlay"></div>
                <?php 
                    $imagehtml = get_the_post_thumbnail($postid, $size); 
                    if(!empty($block_instance['attrs']['parallax_amount'])){
                        $imagehtml = str_replace('img', 'img data-swiper-parallax="'.$block_instance['attrs']['parallax_amount'].'%"', $imagehtml);
                    }
                    echo ''.$imagehtml; ?>
            </div>
        <?php
    }
    ?>
    <?php if (!empty($block_instance['attrs']['container_image'])) {
        ?>
            <div class="gspbgrid_item_inner">
        <?php
    }
    ?>
    <?php
    $block_content = (new \WP_Block(
        $block_instance,
        array(
            'postId'   => $postid,
        )
    )
    )->render(array('dynamic' => false));
    echo $block_content;
    ?>
    <?php if (!empty($block_instance['attrs']['container_image'])) {
        ?>
            </div>
        <?php
    }
    ?>
</<?php echo $tagItem; ?>>