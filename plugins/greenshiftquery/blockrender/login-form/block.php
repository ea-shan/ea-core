<?php


namespace Greenshift\Blocks;
defined('ABSPATH') OR exit;


class LoginForm{

    public function __construct(){
        add_action('init', array( $this, 'init_handler' ));
        $this->action();
    }

    public function init_handler(){
        register_block_type(__DIR__, array(
                'render_callback' => array( $this, 'render_block' ),
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
        'username_label' => array(
            'type' => 'string',
            'default' => 'Username',
        ),
        'username_placeholder' => array(
            'type' => 'string',
            'default' => 'Username',
        ),
        'password_label' => array(
            'type' => 'string',
            'default' => 'Password',
        ),
        'password_placeholder' => array(
            'type' => 'string',
            'default' => 'Password',
        ),
        'remember_me_label' => array(
            'type' => 'string',
            'default' => 'Remember me',
        ),
        'button_label' => array(
            'type' => 'string',
            'default' => 'Login',
        ),
        'lost_password_url' => array(
            'type' => 'string',
            'default' => '',
        ),
        'lost_password_label' => array(
            'type' => 'string',
            'default' => 'Lost Password?',
        ),
        'dont_have_acc_text' => array(
            'type' => 'string',
            'default' => 'Don\'t have an account?',
        ),
        'dont_have_acc_link_text' => array(
            'type' => 'string',
            'default' => 'Sign up',
        ),
        'dont_have_acc_link_url' => array(
            'type' => 'string',
            'default' => '',
        ),
        'logged_url' => array(
            'type' => 'string',
            'default' => '',
        ),
        'logged_text' => array(
            'type' => 'string',
            'default' => '',
        ),
        'logged_url_text' => array(
            'type' => 'string',
            'default' => '',
        ),
		'interactionLayers' => array(
			'type' => 'array',
			'default' => array()
        ),
        'login_already_label' => array(
            'type' => 'string',
            'default' => 'You are already logged in. ',
        ),
        'logout_label' => array(
            'type' => 'string',
            'default' => 'Logout?',
        ),
    );

    protected function action(){
        add_action( 'wp_ajax_gspb_login_form_validation', array( $this, 'gspb_login_form_validation' ) );
        add_action( 'wp_ajax_nopriv_gspb_login_form_validation', array( $this, 'gspb_login_form_validation' ) );
    }

    public function gspb_login_form_validation($settings = array()) {
        global $wp;

        $user_login		= sanitize_user($_POST['username']);
        $user_pass		= sanitize_text_field($_POST['password']);
        $remember 	= !empty($_POST['remember']) ? true : false;

        // Check CSRF token
        if( !check_ajax_referer( 'ajax-gspb-login-nonce', 'gspb-login-form', false) ){
            echo json_encode(array('error' => true, 'message'=> '<div class="error"><i></i>'.esc_html__('Session has expired, please reload the page and try again', 'greenshiftquery').'</div>'));
        }

        // Check if input variables are empty
        elseif(empty($user_login) or empty($user_pass)){
            echo json_encode(array('error' => true, 'message'=> '<div class="error"><i></i>'.esc_html__('Please fill all form fields', 'greenshiftquery').'</div>'));
        }

        else{
            $secure_cookie = (!is_ssl()) ? false : '';
            $user = wp_signon( array('user_login' => $user_login, 'user_password' => $user_pass, 'remember' => $remember ), $secure_cookie );
            if(is_wp_error($user)){
                echo json_encode(array('error' => true, 'message'=> '<div class="error"><i></i>'.$user->get_error_message().'</div>'));
            }
            else{
                echo json_encode(array('error' => false, 'message'=> '<div class="success">'.esc_html__('Login successful, reloading page...', 'greenshiftquery').'</div>'));
            }
        }
        wp_die();
    }

    public function render_block($settings = array(), $inner_content=''){
        extract($settings);

        $blockId = 'gspb_id-' . esc_attr($id);

        $data_attributes = \gspb_getDataAttributesfromDynamic($settings);
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $blockId . ' gspb-login-form-box',
				...$data_attributes
			)
		);

        $out = '<div id="'.$blockId.'" '.$wrapper_attributes.gspb_AnimationRenderProps($animation, $interactionLayers).'>';

        $lost_passworl_res_url = '';
        if(empty($lost_password_url)) {
            $lost_passworl_res_url = esc_url(wp_lostpassword_url());
        } else {
            $lost_passworl_res_url = esc_url($lost_password_url);
        }

        $dont_have_acc_link_url_res = '';
        if(empty($dont_have_acc_link_url)) {
            if(class_exists('Woocommerce')){
                $dont_have_acc_link_url_res = get_permalink(wc_get_page_id('myaccount')). '?action=register';
            } else {
                $dont_have_acc_link_url_res = esc_url(wp_registration_url());
            }
        } else {
            $dont_have_acc_link_url_res = esc_url($dont_have_acc_link_url);
        }

        $userid = get_current_user_id();
        ob_start();
        if(!$userid) {
            ?>
            <form method="POST" class="gspb-login-form">
                <label class="gspb-input-text-label">
                    <span><?php echo sanitize_text_field($username_label); ?></span>
                    <input type="text" name="username" placeholder="<?php echo sanitize_text_field($username_placeholder)?>">
                </label>

                <label class="gspb-input-text-label">
                    <span><?php echo sanitize_text_field($password_label); ?></span>
                    <input type="password" name="password" placeholder="<?php echo sanitize_text_field($password_placeholder); ?>">
                    <?php if(!empty($lost_password_label)):?>
                        <a href="<?php echo $lost_passworl_res_url?>" class="lost-password-link"><?php echo sanitize_text_field($lost_password_label); ?></a>
                    <?php endif;?>
                </label>

                <label>
                    <input type="checkbox" name="remember">
                    <span><?php echo sanitize_text_field($remember_me_label); ?></span>
                </label>

                <button type="submit"><?php echo sanitize_text_field($button_label); ?></button>

                <div class="form-errors"></div>

                <?php if(!empty($dont_have_acc_text) || !empty($dont_have_acc_link_text)):?>
                    <div class="dont-have-account">
                        <?php echo sanitize_text_field($dont_have_acc_text); ?>
                        <a href="<?php echo $dont_have_acc_link_url_res?>">
                            <?php echo sanitize_text_field($dont_have_acc_link_text); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="action" value="gspb_login_form_validation"/>
                <?php wp_nonce_field( 'ajax-gspb-login-nonce', 'gspb-login-form' ); ?>
            </form>
            <?php
        } else {
            global $wp;
            echo '<div class="gspb-login-form-logout">';
            if($logged_url && $logged_url_text && $logged_text){
                echo esc_attr($logged_text).' <a href="' . esc_url($logged_url) . '">' . esc_attr($logged_url_text) . '</a>';
            }else{
                echo esc_attr($login_already_label);
                echo '<a href="' . esc_url(wp_logout_url(home_url( $wp->request ))) . '">' . esc_attr($logout_label) . '</a><br />';
            }
    
            echo '</div>';
        }
        $out .= ob_get_contents();
        ob_get_clean();
        $out .='</div>';
        return $out;
    }
}

new LoginForm;