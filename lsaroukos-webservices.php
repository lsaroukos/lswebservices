<?php
/*
Plugin Name: Lsaroukos WebServices
Description: Creates Custom Post TYpes to manage services povided by a webserver. Such services are client list, domain list, hosting services
Author: Lefteris Saroukos
Version: 0.1
Author URI: https://www.lsaroukos.gr
*/
namespace LSAROUKOS_WEBSERVICES;
include dirname(__FILE__).'\Service.php';
include dirname(__FILE__).'\Client.php';
include dirname(__FILE__).'\Website.php';
include dirname(__FILE__).'\Subscription.php';

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

if(!class_exists( 'LSAROUKOS_WEBSERVICES\WebServices' ) ){
class WebServices{ 
  public $plugin_name; //name of the plugin
  public function activate(){/* called automatically on plugin activation */}  
  public function deactivate(){/* called automatically on plugin deactivation */}

  function __construct(){
    $this->plugin_name=plugin_basename(__FILE__); //set the file name as the name of the plugin
    $this->register();
  }
  
  private function register(){
    /*
    * add a link next to the activate link in plugin page
    * add_filter('plugin_action_links_NAME_OF_PLUGIN',$callfunction);
    */    
//    add_filter("plugin_action_links_$this->plugin_name",array($this,'settings_link'));
    #equeues scripts

    //load lsaroukos_webservice cpt
    $client = new Client();
    $service = new Service();
    $website = new Website();
    $website = new Subscription();
    add_action('admin_menu', array($this,'add_admin_menu_items'));
    add_action('admin_enqueue_scripts', function(){
      if(
        ( ( \strpos($_SERVER['REQUEST_URI'],'wp-admin') && \strpos($_SERVER['REQUEST_URI'],'ls_webclient')  )|| ( \get_post_type() == 'ls_webclient' ) )
      ||( ( \strpos($_SERVER['REQUEST_URI'],'wp-admin') && \strpos($_SERVER['REQUEST_URI'],'ls_webservice')  )|| ( \get_post_type() == 'ls_webservice' ) )
      ||( ( \strpos($_SERVER['REQUEST_URI'],'wp-admin') && \strpos($_SERVER['REQUEST_URI'],'ls_websubscription')  )|| ( \get_post_type() == 'ls_websubscription' ) )
      ||( ( \strpos($_SERVER['REQUEST_URI'],'wp-admin') && \strpos($_SERVER['REQUEST_URI'],'ls_website')  )|| ( \get_post_type() == 'ls_website' ) )
      ){   //if on plugin created page
        wp_enqueue_style('style',plugin_dir_url(__FILE__).'style/style.css');
      }
    });
  }

  /**
   * Add a page to the dashboard menu.
   */
    function add_admin_menu_items() {
      add_menu_page( 
          __( 'LS WebServices', 'lswebservices' ),  //page title
          __( 'LS WebServices', 'lswebservices' ),  //menu title
          'read',                                   //capability
          'lsaroukos-webservices',                  //slug
          '',                                       //url of content to show
         );                                     
    }  
}
}

if(class_exists('LSAROUKOS_WEBSERVICES\WebServices')){
  $webservices = new WebServices();  
}
/*EOF*/
