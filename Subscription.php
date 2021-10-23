<?php 
namespace LSAROUKOS_WEBSERVICES;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
  }
  
if(!class_exists( 'LSAROUKOS_WEBSERVICES\Subscription' ) ){
class Subscription{ 

    function __construct(){
        add_action( 'save_post', array($this,'save_post') ); #verifies nonce before saving post
        add_action("init", array($this, 'create_post_type'));    
        add_filter("manage_ls_websubscription_posts_columns", function($columns){
            $columns = array(
                'client'        =>  __('Client', 'lswebservices'),
                'service'       =>  __('service','lswebservices'),
                'registration'  =>  __('First Registration','lswebservices'),
                'renewal'       =>  __('Last Renewal','lswebservices'),
            );
            return $columns;
        },10,2);

        add_filter('manage_edit-ls_websubscription_sortable_columns', function($columns){
            $columns = array(
                'client'        =>  __('Client', 'lswebservices'),
                'service'       =>  __('service','lswebservices'),
                'registration'  =>  __('First Registration','lswebservices'),
                'renewal'       =>  __('Last Renewal','lswebservices'),
            );
            return $columns;
        });

        add_action('manage_ls_websubscription_posts_custom_column',function($column_name, $post_id){
            global $post;
            $meta = get_post_meta($post_id, 'lswebsubscription',true);
            if($column_name=="client"){
                $client_id = (isset($meta['client']) ? $meta['client'] : -1 );
                $client = get_post($client_id);
                echo "<span>".(isset($client->post_title) ? $client->post_title : 'undefined' )."</span>";
            }else if($column_name=="service"){
                $service_id = (isset($meta['service']) ? $meta['service'] : -1 );
                $service = get_post($service_id);
                $service_meta = get_post_meta($service_id,'lswebservice',true);
                echo "<span>".(isset($service->post_title) ? $service->post_title : 'undefined' )."</span>";
            }else if($column_name=="registration"){
                echo "<span>".(isset($meta['registration']) ? $meta['registration'] : "undefined" )."</span>";
            }else if($column_name=="renewal"){
                $service_id = (isset($meta['service']) ? $meta['service'] : -1 );
                $service_meta = get_post_meta($service_id,'lswebservice',true);
                $duration = $service_meta['duration'];  //duration in days of current service 
                $renewal = (isset($meta['renewal']) ? new \DateTime($meta['renewal']) : new \DateTime() );
                $today = new \DateTime();
                $passed = $today->diff($renewal);
                if( $passed->days-$duration > 0)   //if expired
                    echo "<span class='expired-subscription'>".$renewal->format('Y-m-d')."</span>";
                else if( $passed->days-$duration > -15 )
                    echo "<span class='expiring-soon-subscription'>".$renewal->format('Y-m-d')."</span>";
                else
                    echo "<span class='valid-subscription'>".$passed->days.$renewal->format('Y-m-d')."</span>";
            }
        }, 10, 2);
    }

    /**
     * registers subscription post type
     */
    function create_post_type(){
        $labels = array(
                    'name'          =>  __('Subscriptions','lswebservices'),
                    'add_new_item'  =>  __( 'Add New Subscription', 'lswebservices' ),
        );
        $re = register_post_type('ls_websubscription',array(   
                            'labels'      =>  $labels,
                            'description' =>  'websubscriptions custom post type',
                            'public'      =>  true,
                            'show_in_menu'=>  'lsaroukos-webservices',  //menu entry to be placed as sub-menu
                            'menu_position'=> 2,
                            'rewrite'     =>  array('slug'  =>  'ls-websubscription'),
                            'supports'     => array(''),
                            'register_meta_box_cb'  =>  array($this,'meta_box')
        ));
    }

    /**
     * defines meta ox and registers its content
     */
    function meta_box(){
        /*
         * Generates custom fields to add to the songs editor
         */
          add_meta_box('lswebsubscription-meta','subscription Info',array($this,'meta_box_content'),'ls_websubscription','normal'); 
    }

    function meta_box_content() {
        global $post;  
        $meta = get_post_meta( $post->ID, 'lswebsubscription', true ); #return an array of all the meta information of current post
        #  custom input fields 
        echo '<input type="hidden" name="lswebsubscription_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'">';
        ?>
        <table>
            <tr>
                <td><label for="lswebsubscription[client]"><?php _e('client','lswebservices'); ?></label></td>
                <td><select id="subs_client" name="lswebsubscription[client]" required>
                            <option>---</option>
                            <?php 
                                $clients = get_posts(array(
                                    'post_type' => 'ls_webclient',
                                ));
                                foreach($clients as $client){
                                    echo "<option ".(isset($meta['client']) && $meta['client']==$client->ID ? 'selected':'')." value='".$client->ID."'>".$client->post_title."</option>";
                                }
                            ?>
                        </select>
                </td>
            </tr>
            <tr>
                <td><label for="lswebsubscription[service]"><?php _e('service','lswebservices'); ?></label></td>
                <td><select id="subs_service" name="lswebsubscription[service]" required>
                    <option>---</option>
                    <?php 
                        $services = get_posts(array(
                            'post_type' => 'ls_webservice',
                        ));
                        foreach($services as $service){
                            echo "<option ".(isset($meta['service']) && $meta['service']==$service->ID ? 'selected':'')." value='".$service->ID."'>".$service->post_title."</option>";
                        }
                    ?>
                </select></td>
            </tr>
            <tr>
                <td><label for="lswebsubscription[registration]"><?php _e('first registration', 'lswebservices'); ?></label></td>
                <td><input type="date" name="lswebsubscription[registration]" value="<?php 
                    if( isset($meta['registration']) ) 
                        echo $meta['registration']; 
                ?>"></td>
            </tr>
            <tr>
                <td><label for="lswebsubscription[renewal]"><?php _e('last renewal', 'lswebservices'); ?></label></td>
                <td><input type="date" name="lswebsubscription[renewal]" value="<?php 
                    if( isset($meta['renewal']) ) 
                        echo $meta['renewal']; 
                ?>"></td>
            </tr>
        </table>
        <?php
    }
    
    function save_post  ( $post_id ) {   
        /*
         *  This fucntions verifies the nonce generated for security reasons
        */
        
        if( get_post_type($post_id) !== 'ls_websubscription')
            return $post_id;

        if ( !wp_verify_nonce( $_POST['lswebsubscription_nonce'], basename(__FILE__) ) ) 
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
        
        $old = get_post_meta( $post_id, 'lswebsubscription', true );
        $new = $_POST['lswebsubscription'];
    
        if ( $new && $new !== $old ) {
            update_post_meta( $post_id, 'lswebsubscription', $new );
        } elseif ( '' === $new && $old ) {
            delete_post_meta( $post_id, 'lswebsubscription', $old );
        }
    }
     
}
}     

?>