<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

if (!function_exists('GSAlreadyHot')){
	function GSAlreadyHot( $post_id ) { // test if user liked before
		
		if ( is_user_logged_in() ) { // user is logged in
			global $current_user;
			$user_id = $current_user->ID; // current user
			$meta_USERS = get_post_meta( $post_id, "_user_liked" ); // user ids from post meta
			$liked_USERS = ""; // set up array variable     
			if ( is_array($meta_USERS) && count( $meta_USERS ) != 0 ) { // meta exists, set up values
				$liked_USERS = $meta_USERS[0];
			}       
			if( !is_array( $liked_USERS ) ) // make array just in case
				$liked_USERS = array();         
			if ( in_array( $user_id, $liked_USERS ) ) { // True if User ID in array
				return true;
			}
			return false;       
		} 
		else { // user is anonymous, use IP address for voting  
			$meta_IPS = get_post_meta($post_id, "_user_IP"); // get previously voted IP address
			$ip = gspb_get_user_ip(); // Retrieve current user IP
			$liked_IPS = ""; // set up array variable
			if ( is_array($meta_IPS) && count( $meta_IPS ) != 0 ) { // meta exists, set up values
				$liked_IPS = $meta_IPS[0];
			}
			if ( !is_array( $liked_IPS ) ) // make array just in case
				$liked_IPS = array();
			if ( in_array( $ip, $liked_IPS ) ) { // True is IP in array
				return true;
			}
			return false;
		}   
	}
}
function gspb_query_thumb_counter($atts, $content = null){
	extract(shortcode_atts(array(
		'post_id' => NULL,
		'postfix' => '',
		'type' => 'thumbs',
		'maxtemp' => 100,
		'tempscale' => ''
	), $atts));
	if(!$post_id){
		global $post;
		if(is_object($post)){
			$post_id = $post->ID;
		}
	}
	$like_count = get_post_meta( $post_id, "post_hot_count", true ); // get post likes
    if ( ( !$like_count ) || ( $like_count && $like_count == "0" ) ) { // no votes, set up empty variable
        $count = '0';
    } elseif ( $like_count && $like_count != "0" ) { // there are votes!
        $count = intval( $like_count );
    }
	$out = '';
	$alreadyclass = (GSAlreadyHot($post_id)) ? ' alreadyhot' : ' hotenable';
	$out .='<span data-post_id="'.$post_id.'" data-informer="'.$count.'" data-maxtemp="'.$maxtemp.'" class="gs-thumbsminus'.$alreadyclass.'">';
		if($type == 'thumbs'){
			$out .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.88 105.01"><path d="M4,61.65H32.37a4,4,0,0,0,4-4V4a4.05,4.05,0,0,0-4-4H4A4,4,0,0,0,0,4V57.62a4,4,0,0,0,4,4ZM62.16,98.71a7.35,7.35,0,0,0,4.07,5.65,8.14,8.14,0,0,0,5.56.32,15.53,15.53,0,0,0,5.3-2.71,26.23,26.23,0,0,0,9.72-18.86,57.44,57.44,0,0,0-.12-8.35c-.17-2-.42-4-.76-6.15h20.2a21.57,21.57,0,0,0,9.1-2.32,14.87,14.87,0,0,0,5.6-4.92,12.59,12.59,0,0,0,2-7.52,18.1,18.1,0,0,0-1.82-6.92,21.87,21.87,0,0,0,.54-8.39,9.68,9.68,0,0,0-2.78-5.67,25.28,25.28,0,0,0-1.4-9.44,19.9,19.9,0,0,0-4.5-7,28.09,28.09,0,0,0-.9-5A17.35,17.35,0,0,0,109.5,6h0C106.07,1.14,103.33,1.25,99,1.43c-.61,0-1.26.05-2.26.05H57.39a19.08,19.08,0,0,0-8.86,1.78,20.9,20.9,0,0,0-7,6.06L41,11V56.86l2,.54c5.08,1.37,9.07,5.7,12.16,10.89a76,76,0,0,1,7,16.64V98.2l.06.51Zm6.32.78a2.13,2.13,0,0,1-1-1.57V84.55l-.12-.77a82.5,82.5,0,0,0-7.61-18.24C56.4,59.92,52,55.1,46.37,52.87V11.94a14.87,14.87,0,0,1,4.56-3.88,14.14,14.14,0,0,1,6.46-1.21H96.73c.7,0,1.61,0,2.47-.07,2.57-.11,4.2-.17,5.94,2.28v0a12.12,12.12,0,0,1,1.71,3.74,24.63,24.63,0,0,1,.79,5l.83,1.76a15,15,0,0,1,3.9,5.75,21.23,21.23,0,0,1,1,8.68l-.1,1.59,1.36.84a4.09,4.09,0,0,1,1.64,3,17.44,17.44,0,0,1-.68,7.12l.21,1.94A13.16,13.16,0,0,1,117.51,54a7.34,7.34,0,0,1-1.17,4.39,9.61,9.61,0,0,1-3.59,3.12,16,16,0,0,1-6.71,1.7H79.51l.6,3.18a85.37,85.37,0,0,1,1.22,8.78,51.11,51.11,0,0,1,.13,7.56,20.78,20.78,0,0,1-7.62,14.95,10.29,10.29,0,0,1-3.41,1.78,3,3,0,0,1-2,0ZM22.64,19.71a5.13,5.13,0,1,0-5.13-5.13,5.13,5.13,0,0,0,5.13,5.13Z"/></svg>';
		}else if($type == 'plusminus'){
			$out .='<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 122.875 28.489"><g><path fill-rule="evenodd" clip-rule="evenodd" d="M108.993,0c7.683-0.059,13.898,6.12,13.882,13.805 c-0.018,7.682-6.26,13.958-13.942,14.018c-31.683,0.222-63.368,0.444-95.051,0.666C6.2,28.549-0.016,22.369,0,14.685 C0.018,7.002,6.261,0.726,13.943,0.667C45.626,0.445,77.311,0.223,108.993,0L108.993,0z"/></g></svg>';
		}
	$out .='</span>';
	$out .='<span data-post_id="'.$post_id.'" data-informer="'.$count.'" data-maxtemp="'.$maxtemp.'" class="gs-thumbsplus'.$alreadyclass.'">';
		if($type == 'thumbs'){
			$out .= '<svg x="0px" y="0px" viewBox="0 0 122.88 104.19"><g><path d="M62.63,6.25c0.56-2.85,2.03-4.68,4.04-5.61c1.63-0.76,3.54-0.83,5.52-0.31c1.72,0.45,3.53,1.37,5.26,2.69 c4.69,3.57,9.08,10.3,9.64,18.71c0.17,2.59,0.12,5.35-0.12,8.29c-0.16,1.94-0.41,3.98-0.75,6.1h19.95l0.09,0.01 c3.24,0.13,6.38,0.92,9.03,2.3c2.29,1.2,4.22,2.84,5.56,4.88c1.38,2.1,2.13,4.6,2.02,7.46c-0.08,2.12-0.65,4.42-1.81,6.87 c0.66,2.76,0.97,5.72,0.54,8.32c-0.36,2.21-1.22,4.17-2.76,5.63c0.08,3.65-0.41,6.71-1.39,9.36c-1.01,2.72-2.52,4.98-4.46,6.98 c-0.17,1.75-0.45,3.42-0.89,4.98c-0.55,1.96-1.36,3.76-2.49,5.35l0,0c-3.4,4.8-6.12,4.69-10.43,4.51c-0.6-0.02-1.24-0.05-2.24-0.05 l-39.03,0c-3.51,0-6.27-0.51-8.79-1.77c-2.49-1.25-4.62-3.17-6.89-6.01l-0.58-1.66V47.78l1.98-0.53 c5.03-1.36,8.99-5.66,12.07-10.81c3.16-5.3,5.38-11.5,6.9-16.51V6.76L62.63,6.25L62.63,6.25L62.63,6.25z M4,43.02h29.08 c2.2,0,4,1.8,4,4v53.17c0,2.2-1.8,4-4,4l-29.08,0c-2.2,0-4-1.8-4-4V47.02C0,44.82,1.8,43.02,4,43.02L4,43.02L4,43.02z M68.9,5.48 c-0.43,0.2-0.78,0.7-0.99,1.56V20.3l-0.12,0.76c-1.61,5.37-4.01,12.17-7.55,18.1c-3.33,5.57-7.65,10.36-13.27,12.57v40.61 c1.54,1.83,2.96,3.07,4.52,3.85c1.72,0.86,3.74,1.2,6.42,1.2l39.03,0c0.7,0,1.6,0.04,2.45,0.07c2.56,0.1,4.17,0.17,5.9-2.27v-0.01 c0.75-1.06,1.3-2.31,1.69-3.71c0.42-1.49,0.67-3.15,0.79-4.92l0.82-1.75c1.72-1.63,3.03-3.46,3.87-5.71 c0.86-2.32,1.23-5.11,1.02-8.61l-0.09-1.58l1.34-0.83c0.92-0.57,1.42-1.65,1.63-2.97c0.34-2.1-0.02-4.67-0.67-7.06l0.21-1.93 c1.08-2.07,1.6-3.92,1.67-5.54c0.06-1.68-0.37-3.14-1.17-4.35c-0.84-1.27-2.07-2.31-3.56-3.09c-1.92-1.01-4.24-1.59-6.66-1.69v0.01 l-26.32,0l0.59-3.15c0.57-3.05,0.98-5.96,1.22-8.72c0.23-2.7,0.27-5.21,0.12-7.49c-0.45-6.72-3.89-12.04-7.56-14.83 c-1.17-0.89-2.33-1.5-3.38-1.77C70.04,5.27,69.38,5.26,68.9,5.48L68.9,5.48L68.9,5.48z"/></g></svg>';
		}else if ($type=='plusminus'){
			$out .='<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 122.875 122.648"><g><path fill-rule="evenodd" clip-rule="evenodd" d="M108.993,47.079c7.683-0.059,13.898,6.12,13.882,13.805 c-0.018,7.683-6.26,13.959-13.942,14.019L75.24,75.138l-0.235,33.73c-0.063,7.619-6.338,13.789-14.014,13.78 c-7.678-0.01-13.848-6.197-13.785-13.818l0.233-33.497l-33.558,0.235C6.2,75.628-0.016,69.448,0,61.764 c0.018-7.683,6.261-13.959,13.943-14.018l33.692-0.236l0.236-33.73C47.935,6.161,54.209-0.009,61.885,0 c7.678,0.009,13.848,6.197,13.784,13.818l-0.233,33.497L108.993,47.079L108.993,47.079z"/></g></svg>';
		}
	$out .='</span>';
	$out .='<span id="gs-thumbscount'.$post_id.'" class="gs-thumbscount"><span class="countval">'.$count.'</span><span class="countpostfix">'.$postfix.'</span></span>';
	if($tempscale){
		if ($count >= $maxtemp) {$count = $maxtemp;} 
	
		$out .= '<div id="fonscale'.$post_id.'" class="fonscale">';      
		$out .= '<div id="scaleperc'.$post_id.'" class="scaleperc" data-maxtemp="'.$maxtemp.'" style="width:';
		if ($count >= 0) {
			$out .= ''.($count / $maxtemp * 100).'%">';
		}else{
			$out .= '0%">';
		}
		$out .= '</div></div>';  
	}
	return $out;
}
add_action( 'wp_ajax_nopriv_gshotcounter', 'gshotcounter_function' );
add_action( 'wp_ajax_gshotcounter', 'gshotcounter_function' );

if (!function_exists('gshotcounter_function')){
function gshotcounter_function() {
    $nonce = sanitize_text_field($_POST['hotnonce']);
    if ( ! wp_verify_nonce( $nonce, 'hotnonce' ) )
        die ( 'Nope!' );
    
    if ( isset( $_POST['hot_count'] ) ) {   
        $post_id = intval($_POST['post_id']); // post id
        $posthot = get_post($post_id);
        $postauthor = $posthot->post_author; 
        $post_hot_count = get_post_meta( $post_id, "post_hot_count", true ); // post like count  
        $overall_post_likes = get_user_meta( $postauthor, "overall_post_likes", true ); // get overall post likes of user   
        if ( is_user_logged_in() ) { // user is logged in
            global $current_user;
            $user_id = $current_user->ID; // current user
            $meta_POSTS = get_user_meta( $user_id, "_liked_posts" ); // post ids from user meta
            $meta_USERS = get_post_meta( $post_id, "_user_liked" ); // user ids from post meta
            $liked_POSTS = ""; // setup array variable
            $liked_USERS = ""; // setup array variable          
            if ( count( $meta_POSTS ) != 0 ) { // meta exists, set up values
                $liked_POSTS = $meta_POSTS[0];
            }           
            if ( !is_array( $liked_POSTS ) ) // make array just in case
                $liked_POSTS = array();             
            if ( count( $meta_USERS ) != 0 ) { // meta exists, set up values
                $liked_USERS = $meta_USERS[0];
            }       
            if ( !is_array( $liked_USERS ) ) // make array just in case
                $liked_USERS = array();             
            $liked_POSTS['post-'.$post_id] = $post_id; // Add post id to user meta array
            $liked_USERS['user-'.$user_id] = $user_id; // add user id to post meta array
            $user_likes = count( $liked_POSTS ); // count user likes

            if ($_POST['hot_count'] =='hot') {              
                if ( !GSAlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", ++$post_hot_count ); // +1 count post meta
                    update_post_meta( $post_id, "_user_liked", $liked_USERS ); // Add user ID to post meta
                    update_user_meta( $user_id, "_liked_posts", $liked_POSTS ); // Add post ID to user meta
                    update_user_meta( $user_id, "_user_like_count", $user_likes ); // +1 count user meta    
                    update_user_meta( $postauthor, "overall_post_likes", ++$overall_post_likes ); // +1 count to post author overall likes             
                } 
                else {
                   // update_post_meta( $post_id, "post_hot_count", $post_hot_count+2 );
                }       
            }
            if ($_POST['hot_count'] =='cold') {
                if ( !GSAlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", --$post_hot_count ); // -1 count post meta
                    update_post_meta( $post_id, "_user_liked", $liked_USERS ); // Add user ID to post meta
                    update_user_meta( $user_id, "_liked_posts", $liked_POSTS ); // Add post ID to user meta
                    update_user_meta( $user_id, "_user_like_count", $user_likes ); // -1 count user meta   
                    update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes ); // -1 count to post author overall likes                 
                } 
                else {
                    if(!empty($_POST['heart'])){
                        update_post_meta( $post_id, "post_hot_count", $post_hot_count-1 );
                        update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes );
                        update_user_meta( $user_id, "_user_like_count", $user_likes-1 );
                        
                        $userkeyip = 'user-'.$user_id;
                        unset($liked_USERS[$userkeyip]);
                        update_post_meta( $post_id, "_user_liked", $liked_USERS );

                        $postkeyip = 'post-'.$post_id;
                        unset($liked_POSTS[$postkeyip]);
                        update_user_meta( $user_id, "_liked_posts", $liked_POSTS );                        
                    }
                }                                       
            }           
            
        } else { // user is not logged in (anonymous)
            $ip = gspb_get_user_ip(); // user IP address
            $postidarray = array();
            $guest_likes_transient = get_transient('re_guest_likes_' . $ip);
            $meta_IPS = get_post_meta( $post_id, "_user_IP" ); // stored IP addresses
            $liked_IPS = ""; // set up array variable           
            if ( count( $meta_IPS ) != 0 ) { // meta exists, set up values
                $liked_IPS = $meta_IPS[0];
            }   
            if ( !is_array( $liked_IPS ) ) // make array just in case
                $liked_IPS = array();               
            if ( !in_array( $ip, $liked_IPS ) ) // if IP not in array
                $liked_IPS['ip-'.$ip] = $ip; // add IP to array 

            if ($_POST['hot_count'] =='hot') {              
                if ( !GSAlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", ++$post_hot_count ); // +1 count post meta
                    update_post_meta( $post_id, "_user_IP", $liked_IPS ); // Add user IP to post meta  
                    update_user_meta( $postauthor, "overall_post_likes", ++$overall_post_likes ); // +1 count to post author overall likes   
                    if(empty($guest_likes_transient)) {
                        $postidarray[] = $post_id;
                        set_transient('re_guest_likes_' . $ip, $postidarray, 30 * DAY_IN_SECONDS);
                    } else {
                        if(is_array($guest_likes_transient)){
                            $guest_likes_transient[] = $post_id;
                            set_transient('re_guest_likes_' . $ip, $guest_likes_transient, 30 * DAY_IN_SECONDS);
                        }                   
                    }                                  
                } 
                else {
                    //update_post_meta( $post_id, "post_hot_count", $post_hot_count+2 );
                }       
            }
            if ($_POST['hot_count'] =='cold') {
                if ( !GSAlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", --$post_hot_count ); // -1 count post meta
                    update_post_meta( $post_id, "_user_IP", $liked_IPS ); // Add user IP to post meta   
                    update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes ); // -1 count to post author overall likes                    
                } 
                else {
                    if(!empty($_POST['heart'])){
                        update_post_meta( $post_id, "post_hot_count", $post_hot_count-1 );
                        update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes );   

                        $keyip = 'ip-'.$ip;
                        unset($meta_IPS[$keyip]);
                        update_post_meta( $post_id, "_user_IP", $meta_IPS );
                        $keydelete = array_search($post_id, $guest_likes_transient);
                        unset($guest_likes_transient[$keydelete]);
                        set_transient('re_guest_likes_' . $ip, $guest_likes_transient, 30 * DAY_IN_SECONDS);

                    }
                }                                       
            }
        }
        do_action('rh_overall_post_likes_add');
        do_action('gs_overall_post_likes_add', $post_id);
    }
    exit;
}
}