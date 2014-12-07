<?php
/**
 * New Google Recaptcha
 * 
 */
if(!class_exists('GreCaptchaClass')){
class GreCaptchaClass {
   /**
    * Site Key from google
    * @var string
    */
    private $site_key ='';
    /**
     * Private Key from Google
     */
    private $private_key ='';
    /**
     * final URL
     * @var string 
     */
    private $end_url = 'https://www.google.com/recaptcha/api/siteverify';
    
   /**
    * Primary constructor for all basic setups
    */
    public function __construct() {
        $this->site_key = get_option('bhardwaja_grecaptcha_site_key');
        $this->private_key = get_option('bhardwaja_grecaptcha_private_key');
        
        add_action('wp_enqueue_scripts',array(&$this,'register_google_script'));
        add_action('login_enqueue_scripts',array(&$this,'register_google_script'));
        add_action( 'login_enqueue_scripts', array(&$this,'my_login_stylesheet'));
        
        /**
         * Adds recaptcha on login page and validates it
         */
        if(get_option('bhardwaja_grecaptcha_captcha_on_login') == 'on'){
            add_action('login_form',array(&$this,'recaptcha_in_login_form'));
            add_filter( 'authenticate',array(&$this,'bhardwaja_auth_signon'), 30, 3 );
        }
        /**
         * Adds recaptcha on register page and validates submition
         */
       if(get_option('bhardwaja_grecaptcha_captcha_on_register') == 'on'){
            if(is_multisite()){
               add_action('signup_extra_fields',array(&$this,'recaptcha_in_register_form_multisite'));
               add_filter('wpmu_validate_user_signup',array(&$this, 'signup_user_error'));
           }  else {
               add_action('register_form',array(&$this,'recaptcha_in_register_form'));
               add_filter('registration_errors', array(&$this,'myplugin_check_fields'), 10, 3);
           }
       }
    }
    
    /**
     * Some basic styles on login page to handle the captcha styling
     */
    function my_login_stylesheet() {
        wp_enqueue_style( 'custom-login',PB_URLPATH.'lib/login-style.css' );
       // wp_enqueue_script( 'custom-login', get_template_directory_uri() . '/style-login.js' );
    }
    /**
     * Registers Google script to be enqued
     */
    public function register_google_script(){
        wp_register_script('grecaptcha', 'https://www.google.com/recaptcha/api.js',array(),false,true);
    }
    /**
     * Draws new recaptcha to form 
     * @param string $class class for rendering (For future modifications)
     * @return string
     */
    public function captchaForm($class='g-recaptcha') {
        wp_enqueue_script('grecaptcha');
        return '<div class="'.$class.'" data-sitekey="'.$this->site_key.'"></div>';
    }
    
    /**
     * Verifies response from google
     * @return boolean
     */
    public function verify(){
        $url = add_query_arg(array(
         'remoteip' => $_SERVER['REMOTE_ADDR'],
         'response' => filter_input(INPUT_POST, 'g-recaptcha-response'),
         'secret'   => $this->private_key
        ),  $this->end_url);
       $response = wp_remote_get($url);
       if (! is_wp_error( $response ) ) {
         $resp = json_decode($response['body']);
        if($resp->success){
            return true;
        }
       }
       return false;
    }
    
/**
 * Recaptcha form in login form
 */
function recaptcha_in_login_form() {
    echo $this->captchaForm();
}
/**
 * Recaptcha validation on login page
 * @param WP_Error $user
 * @param type $username
 * @param type $password
 * @return \WP_Error
 */
function bhardwaja_auth_signon($user, $username, $password ) {
     if(filter_input(INPUT_POST, 'wp-submit')){
          if( !$this->verify()){
               if(is_a($user,'WP_User')){
                       $user = new WP_Error();        
                    }
                $user->add('error_captcha', __('ERROR: Error in ReCaptcha. Please try again','bhardwaja'));
                return $user;
            }
        }
      return $user;
}



/**
 * Single site error checking
 * @param type $errors
 * @return type
 */
function myplugin_check_fields($errors) {
  if( ! $this->verify()){
    $errors->add('error_captcha', __('ERROR: Error in ReCaptcha. Please try again','bhardwaja'));
   }
    return $errors;
}
/**
 * Single site form rendering
 */
function recaptcha_in_register_form() {
  echo $this->captchaForm();
}

/**
 * Multisite form rendering
 * @param type $error
 */
function recaptcha_in_register_form_multisite($error) {
    echo $this->captchaForm();
}

/**
 * Multisite error ckecking
 * @param type $signup_user_defaults
 * @return type
 */
function signup_user_error($signup_user_defaults){
    if( ! $this->verify()){
        $signup_user_defaults['errors']->add('error_captcha',__('ERROR: Error in ReCaptcha. Please try again','bhardwaja'));
    }
    return $signup_user_defaults;
}

}
}