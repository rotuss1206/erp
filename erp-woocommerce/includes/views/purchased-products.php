<?php
$user = get_user_by('id', $customer->user_id);
$user_meta = get_user_meta( $customer->user_id );
$points_types = gamipress_get_points_types();
$can_manage = current_user_can( gamipress_get_manager_capability() );
$achievement_types = gamipress_get_achievement_types();
$requirement_types = gamipress_get_requirement_types();


// if($_GET['page'] == 'erp-crm'){
//     // header("Location: /my-admin/users.php");
//     $url = $_SERVER[REQUEST_URI]."&cust_id=".$customer->user_id;
//     var_dump($url);
//     if(!$_GET['cust_id']){
//        header("Location: ".$url); 
//     } 
// }



// var_dump($customer->user_id);

if ( $customer->email ) {

    $args = [
        'meta_key' => '_billing_email',
        'customer' => $customer->email
    ];

} else if ( $customer->phone ) {

    $args = [
        'meta_key' => '_billing_phone',
        'customer' => $customer->phone
    ];

} else if ( $customer->user_id ) {

    $args = [
        'meta_key' => '_customer_user',
        'customer' => $customer->user_id
    ];

} else {
    return;
}

$products = erp_wc_get_customer_purchased_products( $args );
?>

<div style="width:200%" class="postbox customer-latest-purchased-product">
    <div class="erp-handlediv" title="Click to toggle"><br></div>
    <h3 class="erp-hndle"><span><?php _e( 'Purchased Products', 'erp-woocommerce' ) ?></span></h3>
    <div class="inside customer-latest-purchased-product-content">
        <?php if ( ! empty( $products ) ): ?>
            <ul>
                <?php
                foreach ( $products as $product_id => $value ) {

                    $product       = wc_get_product( $product_id );
                    $product_title = $product->get_title();
                    $product_id    = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
                    $item_meta     = new WC_Order_Item_Product( $value['item'], $product );

                    // $variations = array();
                    $variations    = $item_meta->get_formatted_meta_data( '_', true );
                ?>

                <li>
                    <div class="erp-left">
                        <?php echo sprintf( '<a href="%s">%s</a> &times; %d', get_edit_post_link( $product_id ), $product_title, $value['qty'] );
                        foreach ( $variations as $variation ) {
                            echo '<div class="item-variation"></div><div class="item-variation">'. $variation->display_key. ': '.$variation->value.'</div>';
                        }
                        ?>
                    </div>
                    <div class="erp-right"><?php echo wc_price( $value['qty']*$value['line_total'] ); ?></div>
                    <div class="clearfix"></div>
                </li>

                <?php } ?>
            </ul>
        <?php else: ?>
            <p class="not-found"><?php _e( 'No products found', 'erp-woocommerce' ); ?></p>
        <?php endif ?>
    </div>
 </div><!-- .postbox -->

 <div style="width:200%" class="postbox customer-latest-purchased-product">
    <div class="erp-handlediv" title="Click to toggle"><br></div>
    <h3 class="erp-hndle"><span>Points Balances</span></h3>
    <div class="inside customer-latest-purchased-product-content">
        <?php if( empty( $points_types ) && $can_manage ) : ?>

            <span class="description">
                <?php echo sprintf( __( 'No points types configured, visit %s to configure some points types.', 'gamipress' ), '<a href="' . admin_url( 'edit.php?post_type=points-type' ) . '">' . __( 'this page', 'gamipress' ) . '</a>' ); ?>
            </span>

        <?php else : ?>

            <div style="display:block;" class="profile-points gamipress-profile-cards">

                <?php // Filter available to re-enable the default points
                if( apply_filters( 'gamipress_user_points_backward_compatibility', false ) ) :
                    $user_points = gamipress_get_user_points( $user->ID ); 

                    ?>

                    <div style="width:100%;" class="profile-points-wrapper gamipress-profile-card-wrapper">

                        <div class="profile-points profile-points-default gamipress-profile-card">

                            <span class="profile-points-type-name"><?php _e( 'Default Points', 'gamipress' ); ?></span>

                            <div class="profile-points-thumbnail"></div>

                            <span class="profile-points-amount"><?php echo $user_points; ?></span>

                            <?php if( $can_manage ) :
                                // Show an editable form of points ?>

                                <a href="#" class="profile-points-toggle"><?php echo __( 'Edit', 'gamipress' ); ?></a>

                                <div class="profile-points-form-wrapper">

                                    <input type="number" name="user_points" id="user_points" value="<?php echo $user_points; ?>" class="regular-text" data-points-type="" />

                                    <span class="description"><?php echo __( 'Enter a new total will automatically log the change and difference between totals.', 'gamipress' ); ?></span>

                                    <div class="profile-points-form-buttons">
                                        <a href="#" class="button button-primary profile-points-save"><?php echo __( 'Save', 'gamipress' ); ?></a>
                                        <a href="#" class="button profile-points-cancel"><?php echo __( 'Cancel', 'gamipress' ); ?></a>
                                        <span class="spinner"></span>
                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endif; ?>

                <?php foreach( $points_types as $points_type => $data ) :
                    $user_points = gamipress_get_user_points( $user->ID, $points_type ); 
                    ?>

                    <div style="width:100%;" class="profile-points-wrapper gamipress-profile-card-wrapper">

                        <div class="profile-points profile-points-<?php echo $points_type; ?> gamipress-profile-card">

                            <span class="profile-points-type-name"><?php echo $data['plural_name']; ?></span>

                            <div class="profile-points-thumbnail"><?php echo gamipress_get_points_type_thumbnail( $points_type, array( 32, 32 ) ); ?></div>

                            <span class="profile-points-amount"><?php echo gamipress_format_amount( $user_points, $points_type ); ?></span>

                            <?php if( $can_manage ) :
                                // Show an editable form of points ?>

                                <a href="#" class="profile-points-toggle"><?php echo __( 'Edit', 'gamipress' ); ?></a>

                                <div class="profile-points-form-wrapper">

                                    <div class="profile-points-new-balance-input">
                                        <label for="user_<?php echo $points_type; ?>_points"><?php echo __( 'New balance:', 'gamipress' ); ?></label>
                                        <input type="number" name="user_<?php echo $points_type; ?>_points" id="user_<?php echo $points_type; ?>_points" value="<?php echo $user_points; ?>" class="regular-text" data-points-type="<?php echo $points_type; ?>" />
                                        <span class="description"><?php echo __( 'Enter a new total will automatically log the change and difference between totals.', 'gamipress' ); ?></span>
                                    </div>

                                    <label for="user_<?php echo $points_type; ?>_register_points_movement" class="profile-points-register-movement-input-label"><?php echo __( 'Register on user earnings:', 'gamipress' ); ?></label>
                                    <div class="gamipress-switch gamipress-switch-small profile-points-register-movement-input">
                                        <input type="checkbox" name="user_<?php echo $points_type; ?>_register_points_movement" id="user_<?php echo $points_type; ?>_register_points_movement">
                                        <label for="user_<?php echo $points_type; ?>_register_points_movement"><?php echo __( 'Check this option to register this balance movement on user earnings.', 'gamipress' ); ?></label>
                                    </div>

                                    <div class="profile-points-earning-text-input" style="display: none;">
                                        <label for="user_<?php echo $points_type; ?>_points_earning_text"><?php echo __( 'Earning entry text:', 'gamipress' ); ?></label>
                                        <input type="text" name="user_<?php echo $points_type; ?>_points_earning_text" id="user_<?php echo $points_type; ?>_points_earning_text" value="<?php echo __( 'Manual balance adjustment', 'gamipress' ); ?>" class="regular-text" />
                                        <span class="description"><?php echo __( 'Enter the line text to be displayed on user earnings.', 'gamipress' ); ?></span>
                                    </div>

                                    <div class="profile-points-form-buttons">
                                        <a href="#" class="button button-primary profile-points-save"><?php echo __( 'Save', 'gamipress' ); ?></a>
                                        <a href="#" class="button profile-points-cancel"><?php echo __( 'Cancel', 'gamipress' ); ?></a>
                                        <span class="spinner"></span>
                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>
    </div>
 </div><!-- .postbox -->

<div style="width:200%" class="postbox customer-latest-purchased-product">
    <div class="erp-handlediv" title="Click to toggle"><br></div>
    <h3 class="erp-hndle"><span><?php echo current_user_can( gamipress_get_manager_capability() ) ? __( 'User Earnings', 'gamipress' ) : __( 'Your Achievements', 'gamipress' ); ?></span></h3>
    <div class="inside customer-latest-purchased-product-content">

       <?php ct_render_ajax_list_table( 'gamipress_user_earnings',
            array(
                'user_id' => absint( $user->ID ),
                'items_per_page' => $items_per_page,
            ),
            array(
                'views' => false,
                'search_box' => false
            )
        ); ?>
    </div>
 </div><!-- .postbox -->

 <div style="width:200%" class="postbox customer-latest-purchased-product">
    <div class="erp-handlediv" title="Click to toggle"><br></div>
    <h3 class="erp-hndle"><span>Award Achievement</span></h3>
    <div class="inside customer-latest-purchased-product-content">
        <table class="form-table">

        <tr>
            <th><label for="gamipress-award-achievement-type-select"><?php _e( 'Select an achievement type to award:', 'gamipress' ); ?></label></th>
            <td>
                <select id="gamipress-award-achievement-type-select">
                    <option value=""><?php _e( 'Choose an achievement type', 'gamipress' ); ?></option>
                    <?php foreach ( $achievement_types as $slug => $data ) :
                        echo '<option value="'. $slug .'">' . ucwords( $data['singular_name'] ) .'</option>';
                    endforeach; ?>
                </select>
            </td>
        </tr>

    </table>

    <div id="gamipress-awards-options">
        <?php foreach ( $achievement_types as $slug => $data ) : ?>
            <div id="<?php echo esc_attr( $slug ); ?>" data-loaded="false" style="display: none;">
                <span class="spinner is-active"></span>
            </div>
        <?php endforeach; ?>

    </div><!-- #gamipress-awards-options -->
    </div>
 </div><!-- .postbox -->

<?php $requirement_types = gamipress_get_requirement_types(); ?>

 <div style="width:200%" class="postbox customer-latest-purchased-product">
    <div class="erp-handlediv" title="Click to toggle"><br></div>
    <h3 class="erp-hndle"><span>Award Requirement</span></h3>
    <div class="inside customer-latest-purchased-product-content">
        <table class="form-table">

            <tr>
                <th><label for="gamipress-award-requirement-type-select"><?php _e( 'Select a requirement type to award:', 'gamipress' ); ?></label></th>
                <td>
                    <select id="gamipress-award-requirement-type-select">
                        <option value=""><?php _e( 'Choose a requirement type', 'gamipress' ); ?></option>
                        <?php foreach ( $requirement_types as $slug => $data ) :
                            echo '<option value="'. $slug .'">' . ucwords( $data['singular_name'] ) .'</option>';
                        endforeach; ?>
                    </select>
                </td>
            </tr>

        </table>

        <div id="gamipress-awards-options" class="custom_gamipress_awards_options">
            <?php foreach ( $requirement_types as $slug => $data ) : ?>
                <div id="<?php echo esc_attr( $slug ); ?>" data-loaded="false" style="display: none;">
                    <span class="spinner is-active"></span>
                </div>
            <?php endforeach; ?>

        </div><!-- #gamipress-awards-options -->
    </div>
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $customer->user_id; ?>">
 </div><!-- .postbox -->
<script>
    (function($) {
 
        $(document).ready(function(){
            $('.erp-customer-feeds [name="user_id"]').val(<?php echo $customer->user_id; ?>);
            // $('body').on('click', '.custom_gamipress_awards_options .gamipress-award-achievement', function() {
            //     function reload(){
            //         location.reload();
            //     }
            //     setTimeout(reload,5000);
            // });
            // $('body').on('click', '.custom_gamipress_awards_options .gamipress-revoke-achievement', function() {
            //     function reload(){
            //         location.reload();
            //     }
            //     setTimeout(reload,5000);
            // });
        });
     
    })( jQuery );
    
</script>
<style>
    .profile-points-wrapper{
        width:100%!important;
    }
</style>

 