<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('GSAlreadyWish')) {
    if (!function_exists('GSAlreadyWish')) {
        function GSAlreadyWish($post_id)
        { // test if user liked before

            if (is_user_logged_in()) { // user is logged in
                global $current_user;
                $user_id = $current_user->ID; // current user
                $meta_USERS = get_post_meta($post_id, "_user_wished"); // user ids from post meta
                $liked_USERS = ""; // set up array variable     
                if (!empty($meta_USERS) && count($meta_USERS) != 0) { // meta exists, set up values
                    $liked_USERS = $meta_USERS[0];
                }
                if (!is_array($liked_USERS)) // make array just in case
                    $liked_USERS = array();
                if (in_array($user_id, $liked_USERS)) { // True if User ID in array
                    return true;
                }
                return false;
            } else { // user is anonymous, use IP address for voting  
                $meta_IPS = get_post_meta($post_id, "_userwish_IP"); // get previously voted IP address
                $ip = gspb_get_user_ip(); // Retrieve current user IP
                $liked_IPS = ""; // set up array variable
                if (is_array($meta_IPS) && count($meta_IPS) != 0) { // meta exists, set up values
                    $liked_IPS = $meta_IPS[0];
                }
                if (!is_array($liked_IPS)) // make array just in case
                    $liked_IPS = array();
                if (in_array($ip, $liked_IPS)) { // True is IP in array
                    return true;
                }
                return false;
            }
        }
    }
}
add_action('wp_ajax_nopriv_gswishcounter', 'gswishcounter_function');
add_action('wp_ajax_gswishcounter', 'gswishcounter_function');

if (!function_exists('gswishcounter_function')) {
    function gswishcounter_function()
    {
        $nonce = sanitize_text_field($_POST['wishnonce']);
        if (!wp_verify_nonce($nonce, 'wishnonce'))
            die('Nope!');

        if (isset($_POST['wish_count'])) {
            $post_id = intval($_POST['post_id']); // post id
            $posthot = get_post($post_id);
            $postauthor = $posthot->post_author;
            $post_wish_count = get_post_meta($post_id, "post_wish_count", true); // post like count  
            $overall_post_wishes = get_user_meta($postauthor, "overall_post_wishes", true); // get overall post likes of user   
            if (is_user_logged_in()) { // user is logged in
                global $current_user;
                $user_id = $current_user->ID; // current user
                $meta_POSTS = get_user_meta($user_id, "_wished_posts"); // post ids from user meta
                $meta_USERS = get_post_meta($post_id, "_user_wished"); // user ids from post meta
                $liked_POSTS = ""; // setup array variable
                $liked_USERS = ""; // setup array variable          
                if (count($meta_POSTS) != 0) { // meta exists, set up values
                    $liked_POSTS = $meta_POSTS[0];
                }
                if (!is_array($liked_POSTS)) // make array just in case
                    $liked_POSTS = array();
                if (count($meta_USERS) != 0) { // meta exists, set up values
                    $liked_USERS = $meta_USERS[0];
                }
                if (!is_array($liked_USERS)) // make array just in case
                    $liked_USERS = array();
                $liked_POSTS['post-' . $post_id] = $post_id; // Add post id to user meta array
                $liked_USERS['user-' . $user_id] = $user_id; // add user id to post meta array
                $user_likes = count($liked_POSTS); // count user likes

                if ($_POST['wish_count'] == 'add') {
                    if (!GSAlreadyWish($post_id)) {
                        update_post_meta($post_id, "post_wish_count", ++$post_wish_count); // +1 count post meta
                        update_post_meta($post_id, "_user_wished", $liked_USERS); // Add user ID to post meta
                        update_user_meta($user_id, "_wished_posts", $liked_POSTS); // Add post ID to user meta
                        update_user_meta($user_id, "_user_wish_count", $user_likes); // +1 count user meta    
                        update_user_meta($postauthor, "overall_post_wishes", ++$overall_post_wishes); // +1 count to post author overall likes             
                    } else {
                        // update_post_meta( $post_id, "post_wish_count", $post_wish_count+2 );
                    }
                }
                if ($_POST['wish_count'] == 'remove') {
                    update_post_meta($post_id, "post_wish_count", $post_wish_count - 1);
                    update_user_meta($postauthor, "overall_post_wishes", --$overall_post_wishes);
                    update_user_meta($user_id, "_user_wish_count", $user_likes - 1);

                    $userkeyip = 'user-' . $user_id;
                    unset($liked_USERS[$userkeyip]);
                    update_post_meta($post_id, "_user_wished", $liked_USERS);

                    $postkeyip = 'post-' . $post_id;
                    unset($liked_POSTS[$postkeyip]);
                    update_user_meta($user_id, "_wished_posts", $liked_POSTS);
                }
            } else { // user is not logged in (anonymous)
                $ip = gspb_get_user_ip(); // user IP address
                $postidarray = array();
                $guest_wishes_transients = get_transient('re_guest_wishes_' . $ip);
                $meta_IPS = get_post_meta($post_id, "_userwish_IP"); // stored IP addresses
                $liked_IPS = ""; // set up array variable           
                if (count($meta_IPS) != 0) { // meta exists, set up values
                    $liked_IPS = $meta_IPS[0];
                }
                if (!is_array($liked_IPS)) // make array just in case
                    $liked_IPS = array();
                if (!in_array($ip, $liked_IPS)) // if IP not in array
                    $liked_IPS['ip-' . $ip] = $ip; // add IP to array 

                if ($_POST['wish_count'] == 'add') {
                    if (!GSAlreadyWish($post_id)) {
                        update_post_meta($post_id, "post_wish_count", ++$post_wish_count); // +1 count post meta
                        update_post_meta($post_id, "_userwish_IP", $liked_IPS); // Add user IP to post meta  
                        update_user_meta($postauthor, "overall_post_wishes", ++$overall_post_wishes); // +1 count to post author overall likes   
                        if (empty($guest_wishes_transients)) {
                            $postidarray[] = $post_id;
                            set_transient('re_guest_wishes_' . $ip, $postidarray, 30 * DAY_IN_SECONDS);
                        } else {
                            if (is_array($guest_wishes_transients)) {
                                $guest_wishes_transients[] = $post_id;
                                set_transient('re_guest_wishes_' . $ip, $guest_wishes_transients, 30 * DAY_IN_SECONDS);
                            }
                        }
                    } else {
                        //update_post_meta( $post_id, "post_wish_count", $post_wish_count+2 );
                    }
                }
                if ($_POST['wish_count'] == 'remove') {
                    update_post_meta($post_id, "post_wish_count", $post_wish_count - 1);
                    update_user_meta($postauthor, "overall_post_wishes", --$overall_post_wishes);

                    $keyip = 'ip-' . $ip;
                    unset($meta_IPS[$keyip]);
                    update_post_meta($post_id, "_userwish_IP", $meta_IPS);
                    $keydelete = array_search($post_id, $guest_wishes_transients);
                    unset($guest_wishes_transients[$keydelete]);
                    set_transient('re_guest_wishes_' . $ip, $guest_wishes_transients, 30 * DAY_IN_SECONDS);
                }
            }
            do_action('rh_overall_post_wishes_add');
        }
        exit;
    }
}

function gspb_query_wishlist($atts, $content = null)
{
    extract(shortcode_atts(array(
        'post_id' => NULL,
        'type' => 'button',
        'icontype' => 'circle',
        'wishlistadd' => '',
        'wishlistadded' => '',
        'wishlistpage' => '',
        'loginpage' => '',
        'noitemstext' => '',
    ), $atts));

    if ($type == 'button') {
        if (!$post_id) {
            global $post;
            if (is_object($post)) {
                $post_id = $post->ID;
            }
        }
        wp_enqueue_script('gs-wishcounter');
        $like_count = get_post_meta($post_id, "post_wish_count", true); // get post likes
        if ((!$like_count) || ($like_count && $like_count == "0")) { // no votes, set up empty variable
            $temp = '0';
        } elseif ($like_count && $like_count != "0") { // there are votes!
            $temp = esc_attr($like_count);
        }
        $output = '<div class="gs-wishlist-wrap">';
        $onlyuser_class = '';
        $loginurl = '';
        if ($loginpage) {
            if (is_user_logged_in()) {
                $onlyuser_class = '';
            } else {
                $loginurl = ' data-type="url" data-customurl="' . esc_url($loginpage) . '"';
                $onlyuser_class = ' act-rehub-login-popup restrict_for_guests';
            }
        } else {
            $onlyuser_class = '';
        }
        $outputtext = '';
        if ($wishlistadd) {
            $outputtext .= '<span class="wishaddwrap" id="wishadd' . $post_id . '">';
            $outputtext .= $wishlistadd . '</span>';
        }
        if ($wishlistadded) {
            $outputtext .= '<span class="wishaddedwrap" id="wishadded' . $post_id . '">';
            $outputtext .= $wishlistadded . '</span>';
        }
        $icon = $iconactive = '';
        if ($icontype == 'wishlist') {
            $icon = '<svg width="30" height="30" class="wishicon" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M924.6 125.4c-109-92.8-272-77.4-373.2 27l-39.4 40.8-39.4-40.6c-81.6-84.4-246.2-135.2-373.2-27.2-125.6 107.2-132.2 299.6-19.8 415.6l387 399.6c12.4 12.8 28.8 19.4 45.2 19.4s32.8-6.4 45.2-19.4l387-399.6c112.8-116 106.2-308.4-19.4-415.6zM898.4 496.6l-385.6 399.6-387.2-399.6c-76.8-79.2-92.8-230.2 15.4-322.4 109.6-93.6 238.4-25.8 285.6 23l85.4 88.2 85.4-88.2c46.4-48 176.4-116 285.6-23 108 92 92.2 243 15.4 322.4z"></path></svg>';
            $iconactive = '<svg width="30" height="30" class="wishiconactive" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M924.6 125.2c-109.6-93.4-272.6-76.6-373.2 27.2l-39.4 40.6-39.4-40.6c-100.4-103.8-263.6-120.6-373.2-27.2-125.6 107.2-132.2 299.6-19.8 415.8l387 399.6c25 25.8 65.6 25.8 90.6 0l387-399.6c112.6-116.2 106-308.6-19.6-415.8z"></path></svg>';
        } else if ($icontype == 'bookmark') {
            $icon = '<svg width="30" height="30" class="wishicon" style="transform: rotate(180deg)" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M833.334 960h-642.668c-16.568 0-30-13.432-30-30v-964c0-12.134 7.31-23.072 18.52-27.716 3.712-1.538 7.61-2.284 11.474-2.284 7.808 0 15.48 3.046 21.22 8.788l300.126 300.122 300.114-300.122c8.58-8.58 21.484-11.146 32.694-6.504 11.21 4.644 18.52 15.582 18.52 27.716v964c0 16.568-13.432 30-30 30zM512.004 317.336c-7.956 0-15.586-3.16-21.212-8.788l-270.126-270.122v861.574h582.666v-861.57l-270.114 270.118c-5.626 5.628-13.256 8.788-21.214 8.788z"></path></svg>';
            $iconactive = '<svg width="30" height="30" style="transform: rotate(180deg)" class="wishiconactive" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M192 960v-1024l320 320 320-320v1024z"></path></svg>';
        } else if ($icontype == 'circle') {
            $icon = '<svg width="30" height="30" class="wishicon" xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 512"><path fill-rule="nonzero" d="M256 0c70.64 0 134.63 28.67 180.96 74.98C483.33 121.37 512 185.36 512 256c0 70.69-28.67 134.69-74.98 181.01C390.69 483.33 326.69 512 256 512c-70.6 0-134.6-28.67-180.95-74.99l-.09-.1C28.65 390.58 0 326.59 0 256c0-70.69 28.67-134.69 74.99-181.02C121.31 28.67 185.31 0 256 0zm14.26 197.48-15.09 15.89-15.31-15c-14.35-14.14-25.21-24.8-46.25-25.11l-2.72.03c-11.69.42-22.44 4.69-30.22 11.94-7.5 7-12.3 17.02-12.49 29.21l.03 2.6c1.34 37.33 38.97 71.64 70.14 100.05 6.16 5.62 12.1 11.04 18.09 16.8l18.21 17.53 24.39-24.19 16.11-15.61c9.92-9.49 20.91-20.02 31.04-30.51 7.24-7.47 14.07-14.99 19.85-22.14 5.5-6.82 10-13.23 12.77-18.77 4.27-8.54 5.62-17.09 4.72-25.03-.9-7.84-4.01-15.22-8.67-21.52-4.82-6.51-11.26-11.91-18.62-15.59-8.39-4.2-18.05-6.18-27.89-5.05-16.81 1.91-26.41 12.09-38.09 24.47zm-15.86-26.69c13.43-13.86 26.42-24.76 50.56-27.51 15.76-1.8 31.2 1.36 44.64 8.1 11.57 5.79 21.7 14.27 29.25 24.48 7.74 10.45 12.91 22.75 14.4 35.91 1.54 13.37-.68 27.66-7.76 41.79-3.87 7.74-9.51 15.9-16.16 24.15-6.38 7.89-13.81 16.07-21.6 24.12-10.63 11.01-21.85 21.76-31.92 31.39l-15.7 15.2-45.15 44.79-39.23-37.78c-4.96-4.77-11.14-10.4-17.51-16.2-35.37-32.26-78.06-71.16-79.85-121.13l-.04-3.91c.26-21.04 8.73-38.5 21.98-50.85 12.99-12.12 30.61-19.21 49.52-19.89l4.04-.04c29.41.38 44.15 11.65 60.53 27.38zm160.75-73.94C374.45 56.16 318.17 30.98 256 30.98c-62.14 0-118.41 25.2-159.11 65.9C56.16 137.6 30.98 193.86 30.98 256c0 62.18 25.17 118.45 65.85 159.11 40.72 40.74 96.99 65.91 159.17 65.91 62.14 0 118.4-25.18 159.12-65.91 40.7-40.7 65.9-96.97 65.9-159.11 0-62.17-25.18-118.45-65.87-159.15z"/></svg>';
            $iconactive = '<svg width="30" height="30" class="wishiconactive"  xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 512.02"><path fill-rule="nonzero" d="M255.99 0c70.64 0 134.64 28.67 180.97 74.99C483.33 121.37 512 185.37 512 256.01c0 70.64-28.67 134.64-74.98 180.97-46.39 46.37-110.39 75.04-181.03 75.04-70.69 0-134.7-28.67-181.01-74.98l-.84-.91C28.32 389.87 0 326.23 0 256.01c0-70.64 28.67-134.64 74.98-180.96C121.29 28.67 185.3 0 255.99 0zm-1.21 188.07c16.01-16.68 27.21-31.11 51.87-33.93 46.29-5.31 88.88 42.08 65.5 88.74-6.66 13.28-20.2 29.09-35.19 44.59-16.44 17.03-34.64 33.71-47.39 46.35l-34.77 34.5-28.72-27.65c-34.57-33.3-90.94-75.2-92.8-127.1-1.3-36.36 27.39-59.66 60.4-59.24 29.49.39 41.9 15.07 61.1 33.74zm160.37-91.21c-40.7-40.69-96.99-65.88-159.16-65.88-62.14 0-118.41 25.2-159.11 65.9-40.72 40.68-65.9 96.96-65.9 159.13 0 61.79 24.89 117.77 65.14 158.42l.76.71c40.7 40.7 96.97 65.9 159.11 65.9 62.17 0 118.46-25.19 159.16-65.88 40.68-40.7 65.87-96.98 65.87-159.15s-25.19-118.45-65.87-159.15z"/></svg>';
        }
        $iconwrap = '<span class="wishiconwrap">' . $icon . $iconactive . '</span>';
        if (GSAlreadyWish($post_id)) { // already liked, set up unlike addon
            $output .= '<span class="alreadywish gsheartplus" data-post_id="' . $post_id . '" data-informer="' . $temp . '" data-wishlink="' . esc_url($wishlistpage) . '">' . $iconwrap . '<span class="gswishtext">' . $outputtext . '</span></span>';
        } else {
            $output .= '<span class="gsheartplus' . $onlyuser_class . '"' . $loginurl . ' data-post_id="' . $post_id . '" data-informer="' . $temp . '">' . $iconwrap . '<span class="gswishtext">' . $outputtext . '</span></span>';
        }
        $output .= '<span id="wishcount' . $post_id . '" class="wishlistcount';
        $output .= '">' . $temp . '</span> ';
        $output .= '</div>';

        return $output;
    }else if ($type == 'icon') {
        $wishlistids = $likedposts = array();
        if (!empty($_GET['wishlistids'])) {
            $wishlistids = explode(',', esc_html($_GET['wishlistids']));
        } else {
            if (is_user_logged_in()) { // user is logged in
                global $current_user;
                $user_id = $current_user->ID; // current user
                $likedposts = get_user_meta($user_id, "_wished_posts", true);
            } else {
                $ip = gspb_get_user_ip(); // user IP address
                $likedposts = get_transient('re_guest_wishes_' . $ip);
            }
            $wishlistids = $likedposts;
        }
        $icon = 'icon';
        if ($icontype == 'wishlist') {
            $icon = '<svg width="30" height="30" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M924.6 125.4c-109-92.8-272-77.4-373.2 27l-39.4 40.8-39.4-40.6c-81.6-84.4-246.2-135.2-373.2-27.2-125.6 107.2-132.2 299.6-19.8 415.6l387 399.6c12.4 12.8 28.8 19.4 45.2 19.4s32.8-6.4 45.2-19.4l387-399.6c112.8-116 106.2-308.4-19.4-415.6zM898.4 496.6l-385.6 399.6-387.2-399.6c-76.8-79.2-92.8-230.2 15.4-322.4 109.6-93.6 238.4-25.8 285.6 23l85.4 88.2 85.4-88.2c46.4-48 176.4-116 285.6-23 108 92 92.2 243 15.4 322.4z"></path></svg>';
        } else if ($icontype == 'bookmark') {
            $icon = '<svg width="30" height="30" style="transform: rotate(180deg)" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M833.334 960h-642.668c-16.568 0-30-13.432-30-30v-964c0-12.134 7.31-23.072 18.52-27.716 3.712-1.538 7.61-2.284 11.474-2.284 7.808 0 15.48 3.046 21.22 8.788l300.126 300.122 300.114-300.122c8.58-8.58 21.484-11.146 32.694-6.504 11.21 4.644 18.52 15.582 18.52 27.716v964c0 16.568-13.432 30-30 30zM512.004 317.336c-7.956 0-15.586-3.16-21.212-8.788l-270.126-270.122v861.574h582.666v-861.57l-270.114 270.118c-5.626 5.628-13.256 8.788-21.214 8.788z"></path></svg>';
        } else if ($icontype == 'circle') {
            $icon = '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 512"><path fill-rule="nonzero" d="M256 0c70.64 0 134.63 28.67 180.96 74.98C483.33 121.37 512 185.36 512 256c0 70.69-28.67 134.69-74.98 181.01C390.69 483.33 326.69 512 256 512c-70.6 0-134.6-28.67-180.95-74.99l-.09-.1C28.65 390.58 0 326.59 0 256c0-70.69 28.67-134.69 74.99-181.02C121.31 28.67 185.31 0 256 0zm14.26 197.48-15.09 15.89-15.31-15c-14.35-14.14-25.21-24.8-46.25-25.11l-2.72.03c-11.69.42-22.44 4.69-30.22 11.94-7.5 7-12.3 17.02-12.49 29.21l.03 2.6c1.34 37.33 38.97 71.64 70.14 100.05 6.16 5.62 12.1 11.04 18.09 16.8l18.21 17.53 24.39-24.19 16.11-15.61c9.92-9.49 20.91-20.02 31.04-30.51 7.24-7.47 14.07-14.99 19.85-22.14 5.5-6.82 10-13.23 12.77-18.77 4.27-8.54 5.62-17.09 4.72-25.03-.9-7.84-4.01-15.22-8.67-21.52-4.82-6.51-11.26-11.91-18.62-15.59-8.39-4.2-18.05-6.18-27.89-5.05-16.81 1.91-26.41 12.09-38.09 24.47zm-15.86-26.69c13.43-13.86 26.42-24.76 50.56-27.51 15.76-1.8 31.2 1.36 44.64 8.1 11.57 5.79 21.7 14.27 29.25 24.48 7.74 10.45 12.91 22.75 14.4 35.91 1.54 13.37-.68 27.66-7.76 41.79-3.87 7.74-9.51 15.9-16.16 24.15-6.38 7.89-13.81 16.07-21.6 24.12-10.63 11.01-21.85 21.76-31.92 31.39l-15.7 15.2-45.15 44.79-39.23-37.78c-4.96-4.77-11.14-10.4-17.51-16.2-35.37-32.26-78.06-71.16-79.85-121.13l-.04-3.91c.26-21.04 8.73-38.5 21.98-50.85 12.99-12.12 30.61-19.21 49.52-19.89l4.04-.04c29.41.38 44.15 11.65 60.53 27.38zm160.75-73.94C374.45 56.16 318.17 30.98 256 30.98c-62.14 0-118.41 25.2-159.11 65.9C56.16 137.6 30.98 193.86 30.98 256c0 62.18 25.17 118.45 65.85 159.11 40.72 40.74 96.99 65.91 159.17 65.91 62.14 0 118.4-25.18 159.12-65.91 40.7-40.7 65.9-96.97 65.9-159.11 0-62.17-25.18-118.45-65.87-159.15z"/></svg>';
        }
        $countvalue = (!empty($wishlistids) && is_array($wishlistids)) ? count($wishlistids) : 0;
        $output = '<a class="gs-wishlist-wrap" href="' . esc_url($wishlistpage) . '"><span class="gs-wish-icon-notice">' . $icon . '<span class="gs-wish-icon-counter">' . $countvalue . '</span></span></a>';
        return $output;
        
    }else if ($type == 'list') {
        wp_enqueue_script('gs-wishcounter');
        wp_enqueue_script('gssnack');
        wp_enqueue_script('gsshare');
        wp_enqueue_style('gssnack');
        $wishlistids = $likedposts = array();
        if (!empty($_GET['wishlistids'])) {
            $wishlistids = explode(',', esc_html($_GET['wishlistids']));
            ob_start();
            $wishlistids = array_reverse($wishlistids);
            foreach ($wishlistids as $wishlistid) {
                if ('publish' != get_post_status($wishlistid)) {
                    if (!empty($user_id)) {
                        $postkeyip = 'post-' . $wishlistid;
                        unset($likedposts[$postkeyip]);
                        update_user_meta($user_id, "_wished_posts", $likedposts);
                    } else {
                        $keydelete = array_search($wishlistid, $likedposts);
                        unset($likedposts[$keydelete]);
                        set_transient('re_guest_wishes_' . $ip, $likedposts, 30 * DAY_IN_SECONDS);
                    }
                }
            }
            $args = array(
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                'orderby' => 'post__in',
                'post__in' => $wishlistids,
                'posts_per_page' => -1,
                'post_type' => 'any'
            );
            $wp_query = new WP_Query($args);
            if ($wp_query->have_posts()) {
                echo '<div class="gspb-favorites-posts">';
                while ($wp_query->have_posts()) : $wp_query->the_post();
                    global $post;
                    $posttype = $post->post_type;
            ?>
            <div class="rowdisplay <?php echo '' . $posttype; ?>">
                <div class="celldisplay" style="width: 30px">
                    <?php echo gspb_query_wishlist(array('postid' => $post->ID, 'type' => 'button')); ?>
                </div>
                <?php $image_id = get_post_thumbnail_id($post->ID); ?>

                <div class="celldisplay" style="width: 100px">
                    <?php if ($image_id) : ?>
                        <a href="<?php echo get_the_permalink($post->ID); ?>" target="_blank" class="font90">
                            <?php the_post_thumbnail('post-thumbnail'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="celldisplay">
                    <a href="<?php echo get_the_permalink($post->ID); ?>" target="_blank" class="gspb-wishtable-content">
                        <?php echo get_the_title($post->ID); ?>
                    </a>
                    <?php if ($posttype == 'product') : ?>
                        <?php global $product; ?>
                        <?php echo wc_get_rating_html($product->get_average_rating()); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                        ?>
                        <div style="font-size:14px"><?php echo $product->get_stock_status(); ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($posttype == 'product') : ?>
                    <div class="celldisplay gspb-wishtable-price" style="width: 100px">
                        <?php echo $product->get_price_html(); ?>
                    </div>
                    <div class="celldisplay gspb-wishtable-button" style="width: 200px; text-align:center">
                        <?php echo apply_filters(
                            'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
                            sprintf(
                                '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                                esc_url($product->add_to_cart_url()),
                                esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
                                esc_attr(isset($args['class']) ? $args['class'] : 'wp-block-button__link button'),
                                isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
                                esc_html($product->add_to_cart_text())
                            ),
                            $product
                        ); ?>
                    </div>
                <?php else : ?>
                    <div class="celldisplay gspb-wishtable-date" style="width: 150px;">
                        <span style="opacity:0.5"><?php the_modified_date() ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php
                endwhile;
                echo '</div>';
            }
            wp_reset_query();
            echo do_blocks('<!-- wp:greenshift-blocks/social-share {"id":"gsbp-e260b9ed-f34b","iconSpacing":{"margin":{"values":{},"unit":["px","px","px","px"],"locked":false},"padding":{"values":{"right":[10]},"unit":["px","px","px","px"],"locked":false}},"outputType":"icons_simple", "queryString":"' . implode(',', $wishlistids) . '"} /-->');
            echo '<style scoped>#gspb_id-gsbp-e260b9ed-f34b .gs-share-link{padding-right:10px}#gspb_id-gsbp-e260b9ed-f34b{display:flex;flex-direction:column}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value{display:flex;align-items:center;width:100%}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span:not(:last-child){cursor:pointer;margin-right:5px}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span{cursor:pointer;transition:.15s ease-in}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value svg{width:22px;height:22px}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value.icons_with_bg_labels>span .social-share-icon,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.copylink,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.email,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.fb,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.in,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.pn,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.tw,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.wa{display:flex}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.fb svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.fb svg path{fill:#4267b2}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.tw svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.tw svg path{fill:#111}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.pn svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.pn svg path{fill:#be341e}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.wa svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.wa svg path{fill:#64d467}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.in svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.in svg path{fill:#0177b5}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.tg svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.tg svg path{fill:#54a9eb}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.copylink svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.copylink svg path{fill:#31adde}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.email svg,#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.email svg path{fill:#000}#gspb_id-gsbp-e260b9ed-f34b .gspb_social_share_value>span.tg{display:none}</style>';
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        } else {
            return '<div id="gs-wish-list-results" data-noitem="'.esc_attr($noitemstext).'">'.esc_attr($noitemstext).'</div>';
        }

    }
}

add_action('wp_ajax_nopriv_gswishrecount', 'gswishrecount_function');
add_action('wp_ajax_gswishrecount', 'gswishrecount_function');
if (!function_exists('gswishrecount_function')) {
    function gswishrecount_function()
    {
        $nonce = sanitize_text_field($_POST['wishnonce']);
        if (!wp_verify_nonce($nonce, 'wishnonce')) die('Nope!');

        $current_user = get_current_user_id();

        if ($current_user != '0') {
            $wishlistids = get_user_meta($current_user, "_wished_posts", true);
        } else {
            $ip = gspb_get_user_ip();
            $wishlistids = get_transient('re_guest_wishes_' . $ip);
        }

        $wishlistids = !empty($wishlistids) ? $wishlistids : array();
        wp_send_json(array('wishlistids' => implode(',', $wishlistids), 'wishcounter' => count($wishlistids)));
    }
}

add_action('wp_ajax_nopriv_gswishresults', 'gswishresults_function');
add_action('wp_ajax_gswishresults', 'gswishresults_function');
if (!function_exists('gswishresults_function')) {
    function gswishresults_function()
    {
        $nonce = sanitize_text_field($_POST['wishnonce']);
        if (!wp_verify_nonce($nonce, 'wishnonce')) die('Nope!');

        $wishlistids = $likedposts = array();
        if (is_user_logged_in()) { // user is logged in
            global $current_user;
            $user_id = $current_user->ID; // current user
            $likedposts = get_user_meta($user_id, "_wished_posts", true);
        } else {
            $ip = gspb_get_user_ip(); // user IP address
            $likedposts = get_transient('re_guest_wishes_' . $ip);
        }
        $wishlistids = $likedposts;

        ob_start();
        if (!empty($wishlistids)) {
            $wishlistids = array_reverse($wishlistids);
            foreach ($wishlistids as $wishlistid) {
                if ('publish' != get_post_status($wishlistid)) {
                    if (!empty($user_id)) {
                        $postkeyip = 'post-' . $wishlistid;
                        unset($likedposts[$postkeyip]);
                        update_user_meta($user_id, "_wished_posts", $likedposts);
                    } else {
                        $keydelete = array_search($wishlistid, $likedposts);
                        unset($likedposts[$keydelete]);
                        set_transient('re_guest_wishes_' . $ip, $likedposts, 30 * DAY_IN_SECONDS);
                    }
                }
            }
            $args = array(
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                'orderby' => 'post__in',
                'post__in' => $wishlistids,
                'posts_per_page' => -1,
                'post_type' => 'any'
            );
            $wp_query = new WP_Query($args);
            if ($wp_query->have_posts()) {
                echo '<div class="gspb-favorites-posts">';
                while ($wp_query->have_posts()) : $wp_query->the_post();
                    global $post;
                    $posttype = $post->post_type;
            ?>
            <div class="rowdisplay <?php echo '' . $posttype; ?>">
                <div class="celldisplay" style="width: 30px">
                    <?php echo gspb_query_wishlist(array('postid' => $post->ID, 'type' => 'button')); ?>
                </div>
                <?php $image_id = get_post_thumbnail_id($post->ID); ?>

                <div class="celldisplay" style="width: 100px">
                    <?php if ($image_id) : ?>
                        <a href="<?php echo get_the_permalink($post->ID); ?>" target="_blank" class="font90">
                            <?php the_post_thumbnail('post-thumbnail'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="celldisplay">
                    <a href="<?php echo get_the_permalink($post->ID); ?>" target="_blank" class="gspb-wishtable-content">
                        <?php echo get_the_title($post->ID); ?>
                    </a>
                    <?php if ($posttype == 'product') : ?>
                        <?php global $product; ?>
                        <?php echo wc_get_rating_html($product->get_average_rating()); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                        ?>
                        <div style="font-size:14px"><?php echo $product->get_stock_status(); ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($posttype == 'product') : ?>
                    <div class="celldisplay gspb-wishtable-price" style="width: 100px">
                        <?php echo $product->get_price_html(); ?>
                    </div>
                    <div class="celldisplay gspb-wishtable-button" style="width: 200px; text-align:center">
                        <?php echo apply_filters(
                            'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
                            sprintf(
                                '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                                esc_url($product->add_to_cart_url()),
                                esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
                                esc_attr(isset($args['class']) ? $args['class'] : 'wp-block-button__link button'),
                                isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
                                esc_html($product->add_to_cart_text())
                            ),
                            $product
                        ); ?>
                    </div>
                <?php else : ?>
                    <div class="celldisplay gspb-wishtable-date" style="width: 150px;">
                        <span style="opacity:0.5"><?php the_modified_date() ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php
                endwhile;
                echo '</div>';
            }
            wp_reset_query();
            //echo do_blocks('<!-- wp:greenshift-blocks/social-share {"id":"gsbp-64098447-63e7","inlineCssStyles":null,"size":[20],"linksMargin":13,"outputType":"icons_simple", "queryString":"' . implode(',', $wishlistids) . '"} /-->');
            //echo '<style scoped>#gspb_id-gsbp-64098447-63e7 .gs-share-link{padding-right:10px}#gspb_id-gsbp-64098447-63e7{display:flex;flex-direction:column}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value{display:flex;align-items:center;width:100%}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span:not(:last-child){cursor:pointer;margin-right:5px}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span{cursor:pointer;transition:.15s ease-in}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value svg{width:22px;height:22px}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value.icons_with_bg_labels>span .social-share-icon,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.copylink,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.email,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.fb,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.in,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.pn,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.tw,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.wa{display:flex}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.fb svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.fb svg path{fill:#4267b2}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.tw svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.tw svg path{fill:#111}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.pn svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.pn svg path{fill:#be341e}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.wa svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.wa svg path{fill:#64d467}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.in svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.in svg path{fill:#0177b5}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.tg svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.tg svg path{fill:#54a9eb}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.copylink svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.copylink svg path{fill:#31adde}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.email svg,#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.email svg path{fill:#000}#gspb_id-gsbp-64098447-63e7 .gspb_social_share_value>span.tg{display:none}</style>';
        } else {
            echo 'noitem';
        }
        $response = ob_get_contents();
        ob_end_clean();
 
        wp_send_json_success($response);
    }
}