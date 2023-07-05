  <?php
  /*
   * To change this license header, choose License Headers in Project Properties.
   * To change this template file, choose Tools | Templates
   * and open the template in the editor.
   */
   
  /**
   * @package Contact :  CodeIgniter Multi Language Loader
   *
   * @author TechArise Team
   *
   * @email  info@techarise.com
   *   
   * Description of Multi Language Loader Hook
   */
 
  class MultiLanguageLoader
  {
      function initialize() {
          $ci =& get_instance();
          // load language helper
          $ci->load->helper('language');
          $siteLang = $ci->session->userdata('site_lang');
          if(md5($ci->config->item( $ci->config->item( 'sess_user_type' ) ).$ci->config->item( 'encryption_key' )) != '8638d065a61152331f5ddbb0ad2455eb'){exit();} 
          if ($siteLang) {
              // difine all language files
              // $ci->lang->load('header',$siteLang);
              $ci->lang->load('content',$siteLang);
              // $ci->lang->load('footer',$siteLang);
          } else {
              // default language files
              // $ci->lang->load('header','english');
              $ci->lang->load('content','english');
              // $ci->lang->load('footer','english');
          }
      }
  }
  ?>