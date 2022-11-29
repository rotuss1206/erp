<?php
namespace WeDevs\ERP\Awesome_Support;

/**
 * Widget class
 *
 * @since 1.0.0
 *
 * @package WPERP|Awesome Support
 */
class Widget{

    public function __construct() {
        add_action('erp_crm_contact_left_widgets', [$this, 'show_tickets']);
    }

    /**
     * Show recent tickets by the customer
     *
     * @since 1.0.0
     */
    public function show_tickets(){
        ob_start();
        ?>
        <div class="postbox erp-as-activity">
            <div class="erp-handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
            <h3 class="erp-hndle"><span><?php _e( 'Recent tickets on Awesome Support', 'erp' ); ?></span></h3>
            <div class="inside">
                <?php
                $user_id = $this->get_customer_user_id();
                global $wpdb;
                $sql = "SELECT ID FROM $wpdb->posts WHERE post_author = '$user_id' ORDER BY post_date DESC LIMIT 5";
                $tickets = $wpdb->get_results($sql );
                if( $user_id && $tickets){
                    $tickets = wp_list_pluck($tickets, 'ID');
                    $list = '';
                    foreach ($tickets as $ticket){
                        $post = get_post($ticket);
                        $link  = get_the_permalink($ticket);
                        $title = $post->post_title;
                        $status = get_post_meta($ticket, '_wpas_status', true);
                        $message = explode(' ', $post->post_content);
                        $taken = array_slice($message, 0, 20);
                        $list .= '<li class="link-to-original">';
                        $list .= '<div class="ticket-meta">';
                        $list .= "<a href='{$link}' target='_blank'>#{$ticket} {$title}</a><span class='ticket-status {$status}'>{$status}</span> ";
                        $list .= '</div>';
                        $list .= implode(' ', $taken);
                        $list .= '</li>';
                    }

                    echo '<ul>'.$list.'</ul>';
                    $link = get_admin_url(null, sprintf('edit.php?post_type=ticket&author=%d', $user_id));
                    echo '<a href="'.$link.'" class="erp-awesome-support-more">View all tickets by the user</a>';
                }else{
                    _e('No activity found', 'erp-awesome-support');
                }
                ?>
            </div>
        </div><!-- .postbox -->
        <?php
        $output = ob_get_contents();
        ob_get_clean();
        echo $output;
    }

    protected function get_customer_user_id(){
        if(isset($_GET['id']) && (!empty($_GET['id']))){
            $contact_id = $_GET['id'];
            $contact = new \WeDevs\ERP\CRM\Contact( absint( $contact_id ), 'contact' );
            if($contact && $contact->user_id){
                return $contact->user_id;
            }
        }

        return false;
    }
}
