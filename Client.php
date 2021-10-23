<?php 
namespace LSAROUKOS_WEBSERVICES;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
  }
  
if(!class_exists( 'LSAROUKOS_WEBSERVICES\Client' ) ){
class Client{ 

    function __construct(){
        add_action( 'save_post', array($this,'save_post') ); #verifies nonce before saving post
        add_action("init", array($this, 'create_ls_webclient_post_type'));    
        add_action("admin_enqueue_scripts",array($this,'register_scripts'));

        add_filter('manage_ls_webclient_posts_columns', function($columns) {
            array_splice( $columns, 2, 0, ['balance' => __('balance','lswebservices')] );
            return $columns;
        });
        add_action('manage_ls_webclient_posts_custom_column', function($column_key, $post_id) {
            global $post;  
            $meta = get_post_meta( $post_id, 'lswebclient', true ); #return an array of all the meta information of current post
        
            if ($column_key == 0) {
                $balance = ( isset($meta['balance']) ? $meta['balance'] : 'undefinded' );
                echo '<span style="color:'.( $balance<0 ? "var(--negative)" : "var(--positive)" ).';">'.$balance.'€</span>';
            }
        }, 1, 2);
}

    /**
     * register scripts and styles
     */
    function register_scripts(){
        if( ( \strpos($_SERVER['REQUEST_URI'],'wp-admin') && \strpos($_SERVER['REQUEST_URI'],'ls_webclient')  )
        || ( \get_post_type() == 'ls_webclient' ) ){   //if on client page
            wp_enqueue_script('client-actions',plugin_dir_url( __FILE__ ).'js/client_actions.js');
            wp_enqueue_style('client-style',plugin_dir_url(__FILE__).'style/clientStyle.css');
        }
    }

    function create_ls_webclient_post_type(){
        $labels = array(
                    'name'          =>  __('Clients','lswebservices'),
                    'add_new_item'  =>  __( 'Add New Client', 'lswebservices' ),
        );
        register_post_type('ls_webclient',array(   
                            'labels'      =>  $labels,
                            'description' =>  'clients custom post type',
                            'public'      =>  true,
                            'show_in_menu'=>  'lsaroukos-webservices',  //menu entry to be placed as sub-menu
                            'menu_position'=> 1,
                            'rewrite'     =>  array('slug'  =>  'ls-webclients'),
                            'supports'    =>  array('title'),
                            'register_meta_box_cb'  =>  array($this,'meta_box')
        ));
    }

    function meta_box(){
        /*
         * Generates custom fields
         */
          add_meta_box('lswebclients-info','Client Info',array($this,'meta_box_content'),'ls_webclient','normal'); 
          add_meta_box('lswebclients-transactions','Client Transactions',array($this,'transactions_meta_content'),'ls_webclient','normal'); 
    }

   function meta_box_content() {
        global $post;  
         $meta = get_post_meta( $post->ID, 'lswebclient', true ); #return an array of all the meta information of current post
        #  custom input fields 
        echo '
          <input type="hidden" name="lswebclient_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'">  
          <div class="client-info">
            <table>
                <tr>
                    <th colspan="4">
                        <input name="lswebclient[name]" placeholder="'.__('name','lswebservices').'" value="'.((is_array($meta) && isset($meta['name']))?$meta['name']:'' ).'" />
                        <input name="lswebclient[sirname]" placeholder="'.__('surname','lswebservices').'" value="'.((is_array($meta) && isset($meta['sirname']))?$meta['sirname']:'' ).'" />
                    </th>
                </tr>
                <tr>
                    <td><label "lswebclient[taxid]" >'.__("tax id","lswebservices").'</label></td>
                    <td colspan="3"><input name="lswebclient[taxid]" value="'.((is_array($meta) && isset($meta['taxid']))?$meta['taxid']:'' ).'" /></td>
                </tr>
                <tr>
                    <td><label "lswebclient[tax_auth]" >'.__("tax authority","lswebservices").'</label></td>
                    <td colspaln="3"><input name="lswebclient[tax_auth]" value="'.((is_array($meta) && isset($meta['tax_auth']))?$meta['tax_auth']:'' ).'" /></td>
                </tr>
                <tr>
                    <td><label "lswebclient[address]" >'.__("address","lswebservices").'</label></td>
                    <td colspan="3"><input name="lswebclient[address]" value="'.((is_array($meta) && isset($meta['address']))?$meta['address']:'' ).'" /></td>
                </tr>
                <tr>
                    <td><label "lswebclient[phone]" >'.__("phone","lswebservices").'</label></td>
                    <td colspan="3"><input type="phone" name="lswebclient[phone]" value="'.((is_array($meta) && isset($meta['phone']))?$meta['phone']:'' ).'" /></td>
                </tr>
                <tr>
                    <td><label "lswebclient[email]" >'.__("email","lswebservices").'</label></td>
                    <td colspan="3"><input name="lswebclient[email]" type="email" value="'.((is_array($meta) && isset($meta['email']))?$meta['email']:'' ).'" /></td>
                </tr>
            </table>
            <div>
                <div class="total-info balance">
                    <label>'.__('balance','lsawebservices').'</label>
                    <p> <span id="balance-amount">'.((is_array($meta) && isset($meta['balance']))?$meta['balance']:0 ).'</span>€</p>
                    <input id="balance-input" hidden name="lswebclient[balance]" value="'.((is_array($meta) && isset($meta['balance']))?$meta['balance']:0 ).'">
                </div>
                <table style="width:250px">
                    <tr>
                        <td>
                            <div class="total-info charged">
                                <p>'.__('charged:','lsawebservices').' <span id="charged-amount">'.((is_array($meta) && isset($meta['charged']))?$meta['charged']:0 ).'</span>€</p>
                                <input id="charged-input" hidden name="lswebclient[charged]" value="'.((is_array($meta) && isset($meta['charged']))?$meta['charged']:0 ).'">
                            </div>
                        </td>
                        <td>
                            <div class="total-info payed">
                                <p>'.__('payed:','lsawebservices').' <span  id="payed-amount">'.((is_array($meta) && isset($meta['payed']))?$meta['payed']:0 ).'</span>€</p>
                                <input id="payed-input" hidden name="lswebclient[payed]" value="'.((is_array($meta) && isset($meta['payed']))?$meta['payed']:0 ).'">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
        ';
    }
    
    function transactions_meta_content() {
        global $post;  
        $meta = get_post_meta( $post->ID, 'lswebtransactions', true ); #return an array of all the meta information of current post
        //$meta = {"transactions":[{"description":...,"amount":...,"date":...},...]}
        
        echo '<h1 style="padding:30px 0;">'.__("Transactions","lswebservices").'</h1>';
        $transactions = ($meta!=="" ? json_decode($meta) : null);
        
        echo "<table id='transactions_list'>
            <tr>
                <th>".__('description','lswetransactions')."</th>
                <th>".__('amount','lswetransactions')."</th>
                <th>".__('date','lswetransactions')."</th>
                <th></th>
            </tr>
        ";
        $total = 0;
        $charged = 0;
        $payed = 0;
        if($transactions!==null)
            foreach($transactions->transactions as $details){  //TODO: pagination. retrieve 10 first, then next 10 etc.
                /*{obj}$transaction = { 
                    description :   ...,
                    amount  :   ...,
                    date    :   ..., 
                }
                */
                $amount = (float)$details->amount;
                if( $amount>=0 )
                    $payed = $payed + $amount;
                else 
                    $charged = $charged + $amount;
                $total = $total + $amount;
                echo '
                    <tr '.($details->amount>=0 ? 'class="income"': 'class="outcome"').' data-transaction=\'{"description":"'.$details->description.'","amount":"'.$details->amount.'","date":"'.$details->date.'"}\' data-amount="'.$details->amount.'">
                        <td>'.( isset($details->description) ? $details->description : '' ).'</td>
                        <td>'.( isset($details->amount) ? $details->amount : '' ).'€</td>
                        <td>'.( isset($details->date) ? $details->date : '' ).'</td>
                        <td onclick="deleteEntry(this.parentNode);">X</td>
                    </tr>
                ';
            }
        echo "</table>";
        ?>
        <textarea id="transactions" name="transactions">
            <?php 
                $stored_transactions = get_post_meta($post->ID, 'lswebtransactions', true);
                if( $stored_transactions == '' )
                    echo '{"transactions":[]}';
                else
                    echo $stored_transactions;
            ?>
        </textarea> <!-- json encoded new entries -->
            <h4 style="padding-top:50px;"><?php _e('new transaction','lswebservices') ?></h4>
            <div id="new-transaction">
                <p>
                    <label for="trans_description"><?php _e('description','lswebservices');?>:</label>&nbsp;
                    <textarea id="trans_description" name="trans_description"></textarea>
                </p>
                <p>
                    <label for="trans_amount"><?php _e('price (€)','lswebservice'); ?>:</label>&nbsp;
                    <input id="trans_amount" name="trans_amount" type="number" step="0.01">
                </p>
                <p>
                    <label for="trans_date"><?php _e('date','lswebservice'); ?>:</label>&nbsp;
                    <input id="trans_date" name="trans_date" type="date">
                </p>
                <p><button class="button button-primary button-large" type="button" onclick="addTransaction();"><?php _e('add','lswebservice'); ?></button></p>
            </div>
        <?php
    }

    function save_post  ( $post_id ) {   
        /*
         *  This fucntions verifies the nonce generated for security reasons
        */
        if( get_post_type($post_id) !== 'ls_webclient')
            return $post_id;

        if ( !wp_verify_nonce( $_POST['lswebclient_nonce'], basename(__FILE__) ) ) 
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
        
        $old = get_post_meta( $post_id, 'lswebclient', true );
        $new = $_POST['lswebclient'];
    
        if ( $new && $new !== $old ) {
            update_post_meta( $post_id, 'lswebclient', $new );
        } elseif ( '' === $new && $old ) {
            delete_post_meta( $post_id, 'lswebclient', $old );
        }
        $new_transactions = $_POST['transactions'] ;    //$transactions->newEntries[0]
        $old_transactions = get_post_meta('lswebtransactions');
        if( $new_transactions !== $old_transactions )
            update_post_meta( $post_id, 'lswebtransactions', $new_transactions );
        elseif ($new_transactions==='' && $old_transactions )
            delete_post_meta($post_id, 'lswebtransactions', $old_transactions);
    }
     
}
}     

?>