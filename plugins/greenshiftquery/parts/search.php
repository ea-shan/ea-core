<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<?php
$block_instance = rawurldecode(stripslashes($block_instance));
$block_instance = json_decode($block_instance, true);
//print_r($block_instance);
if(is_array($block_instance)){
    foreach($block_instance as $block){
        $block_content = (new \WP_Block(
            $block,
            array(
                'postId'   => $postid,
            )
        )
        )->render(array('dynamic' => true));
        echo $block_content;
    }
}
?>