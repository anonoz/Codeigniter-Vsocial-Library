<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  CodeIgniter Vsocial Library
 *  Coz fuck me thats why
 *
 *  This library is written based on the vSocial API v1 Docs.
 *  Please check with the API doc that Volcanic gave you.
 *  I am not responsible for any screwups if you insist of using this
 *  library.
 *
 *  @author Anonoz Chong -> www.anonoz.com + www.github.com/anonoz
 *  @version 0.1 (2013-10-23)
 */
class Vsocial {

  private $CI;
  private $client_id;
  private $app_id;

  function __construct()
  {
    //  Get Codeigniter Instance
    $this->CI =& get_instance();

    //  Load some dependencies
    $this->CI->load->library('session');

    //  Load the config client ID and App ID
    $this->client_id = $this->CI->config->item('vsocial_clientid');
    $this->app_id = $this->CI->config->item('vsocial_appid');
  }

  /**
   *  Generate a Vsocial Access Token and store in session
   *
   *  @param string $fb_userid
   *  @param string $fb_accesstoken
   *  @return void
   *  @author Anonoz Chong <everything@anonoz.com>
   */
  function login($fb_userid, $fb_accesstoken)
  {
    //  Assemble API Request URL
    $target_url =   "https://";
    $target_url .=  $this->client_id;
    $target_url .=  ".vsocial.com/api/v1//vsoc_access_token?";
    $target_url .=  "vsocAppId=" . $this->app_id;
    $target_url .=  "&fbId=" . $fb_userid;
    $target_url .=  "&fbAccessToken=" . $fb_accesstoken;

    //  cURL the request
    $ch = curl_init($target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    $return_json = curl_exec($ch);

    //  Decode JSON
    $return = json_decode($return_json, true);

    //  If SUCCESS
    if (isset($return['status']) and $return['status'] == 'SUCCESS')
    {

      //  Record the access tokens into session
      if (isset($return['object']['vsoc_access_token']))
      {
        $this->CI->session->set_userdata('vsoc_access_token', $return['object']['vsoc_access_token']);
      }

    }

  }

  /**
   *  Record a share event
   *  
   *  @param  $share_type   Can be "FB_POST_TO_WALL","FB_INVITE_FRIENDS","TWITTER","TWITTER_CLICKOUT"
   *  @param  $provider_id  The post ID provided by social networks. Eg Share to wall, "123456789_123456789123"
   *  @param  $provider_response
   *  @param  $extra_app_details  Extra message that gos along, can be anything we want to add
   *  @param  $url          The URL that this share instance shares
   *  @return void
   *  @author Anonoz Chong <everything@anonoz.com>
   */
  function share($share_type = "FB_POST_TO_WALL", 
                 $provider_id, 
                 $provider_response = "200", 
                 $extra_app_details = "", 
                 $url = "")
  {
    //  Assemble Resource URI
    $target_url = "http://" . $this->client_id . ".vsocial.com/api/v1/me/share/";

    //  Preparing POST data
    $post_fields = array(
      "vsoc_access_token" =>  $this->session->userdata('vsoc_access_token'),
      "providerId"  =>  $provider_id,
      "providerResponse"  =>  $provider_response,
      "extraAppDetails" =>  $extra_app_details,
      "shareType" =>  $share_type,
      "url" =>  $url
      );

    //  URL Encode all POST fields
    foreach ($post_fields as $key => $value) {$post_string .= $key . '=' . urlencode($value) . '&';}
    rtrim($post_string, '&');

    //  Initiate cURL
    $ch = curl_init($target_url);

    curl_setopt($ch, CURLOPT_POST, count($post_fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //Execute cURL
    $return_json = curl_exec($ch);


  }

  /**
   *  Record a referral, ONLY on Facebook
   *  
   *  @param  $facebook_id  The Facebook User ID of the referrer
   *  @return void
   *  @author Anonoz Chong <everything@anonoz.com>
   */
  function referral($facebook_id)
  {
    //  Assemble Resource URI
    $target_url = "http://" . $this->client_id . ".vsocial.com/api/v1/me/referral/";

    //  Preparing POST data
    $post_fields = array(
      "vsoc_access_token" =>  $this->session->userdata('vsoc_access_token'),
      "referrerFacebookId"  =>  $facebook_id,
      );

    //  URL Encode all POST fields
    foreach ($post_fields as $key => $value) {$post_string .= $key . '=' . urlencode($value) . '&';}
    rtrim($post_string, '&');

    //  Initiate cURL
    $ch = curl_init($target_url);

    curl_setopt($ch, CURLOPT_POST, count($post_fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //  Execute cURL
    $return_json = curl_exec($ch);
  }

}