<?php 
namespace LSAROUKOS_WEBSERVICES;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
  }
  
if(!class_exists( 'LSAROUKOS_WEBSERVICES\Website' ) ){
class Website{ 

    function __construct(){
        add_action("init", array($this, 'create_post_type'));    
    }


    function create_post_type(){
        $labels = array(
                    'name'          =>  __('Websites','lswebservices'),
                    'add_new_item'  =>  __( 'Add New Websites', 'lswebservices' ),
        );
        $re = register_post_type('ls_website',array(   
                            'labels'      =>  $labels,
                            'description' =>  'websites custom post type',
                            'public'      =>  true,
                            'show_in_menu'=>  'lsaroukos-webservices',  //menu entry to be placed as sub-menu
                            'menu_position'=> 2,
                            'rewrite'     =>  array('slug'  =>  'ls-website'),
                            'supports'    =>  array('title','editor'),
        ));
    }
   
}
}     

?>