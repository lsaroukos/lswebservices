<?php 
namespace LSAROUKOS_WEBSERVICES;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
  }
  
if(!class_exists( 'LSAROUKOS_WEBSERVICES\Service' ) ){
class Service{ 

    function __construct(){
        add_action( 'save_post', array($this,'save_post') ); #verifies nonce before saving post
        add_action("init", array($this, 'create_service_post_type'));

        add_filter("manage_ls_webservice_posts_columns",function($columns){
            $columns["duration"]  = __("Duration (days)","lswebservices");
            $columns["price"]     = __("Price (€)","lswebservices");
            unset($columns['date']);
            return $columns;
        },10,2);
        add_filter("manage_edit-ls_webservice_sortable_columns",function($columns){
            $columns["duration"] = "duration";
            $columns["price"] = "price";
            return $columns;
        },10,2);    
        add_action("manage_ls_webservice_posts_custom_column", function($column_key,$post_id){
            global $post;
            $meta = get_post_meta($post_id, 'lswebservice', true);
            if($column_key=="duration"){
                echo "<span>".(isset($meta['duration']) ? $meta['duration'] : 'undefined' )."</span>";
            }
            if($column_key=="price"){
                echo "<span>".(isset($meta['price']) ? $meta['price'] : 'undefined' )."€</span>";
            }
        },1,2);        
    }


    function create_service_post_type(){
        $labels = array(
                    'name'          =>  __('Services','lswebservices'),
                    'add_new_item'  =>  __( 'Add New Service', 'lswebservices' ),
        );
        $re = register_post_type('ls_webservice',array(   
                            'labels'      =>  $labels,
                            'description' =>  'webservices custom post type',
                            'public'      =>  true,
                            'show_in_menu'=>  'lsaroukos-webservices',  //menu entry to be placed as sub-menu
                            'menu_position'=> 2,
                            'rewrite'     => true,
                            'supports'    =>  array('title'),
                            'register_meta_box_cb'  =>  array($this,'meta_box')
        ));
    }

    function meta_box(){
        /*
         * Generates custom fields to add to the songs editor
         */
          add_meta_box('lswebservice-meta','Service Info',array($this,'meta_box_content'),'ls_webservice','normal'); 
    }

    function meta_box_content() {
        global $post;  
        $meta = get_post_meta( $post->ID, 'lswebservice', true ); #return an array of all the meta information of current post
        #  custom input fields 
        echo '
          <input type="hidden" name="lswebservice_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'">  
          <table>
            <tr>
                <td><label for="lswebservice[price]" >'.__("price (€)","lswebservices").'</label></td>
                <td><input name="lswebservice[price]" type="number" step="0.01" value="'.((is_array($meta) && isset($meta['price']))?$meta['price']:'' ).'" /></td>
            </tr>
            <tr>
                <td><label for="lswebservice[duration]" >'.__("duration in days (-1 for infinite)","lswebservices").'</label></td>
                <td><input name="lswebservice[duration]" type="number" step="1" value="'.((is_array($meta) && isset($meta['duration']))?$meta['duration']:'' ).'" /></td>
            </tr>
          </table>
        ';
    }
    
    function save_post  ( $post_id ) {   
        /*
         *  This fucntions verifies the nonce generated for security reasons
        */
        
        if( get_post_type($post_id) !== 'ls_webservice')
            return $post_id;

        if ( !wp_verify_nonce( $_POST['lswebservice_nonce'], basename(__FILE__) ) ) 
            return $post_id; 
        
        // check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return $post_id;
        
        // check permissions          if ( 'page' === $_POST['post_type'] ) {
        if ( !current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;  
        }
        
        $old = get_post_meta( $post_id, 'lswebservice', true );
        $new = $_POST['lswebservice'];
    
        if ( $new && $new !== $old ) {
            update_post_meta( $post_id, 'lswebservice', $new );
        } elseif ( '' === $new && $old ) {
            delete_post_meta( $post_id, 'lswebservice', $old );
        }
    }
     
}
}     

?>