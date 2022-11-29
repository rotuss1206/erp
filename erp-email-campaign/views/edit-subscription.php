<?php if ( $subscription_updated ): ?>
    <div class="alert alert-success" role="alert">
        <p><?php _e( 'Subscription update successfully.', 'erp-email-campaign' ); ?></p>
    </div>
<?php endif; ?>


<form action="<?php echo ecamp_edit_subscription_link( $hash ); ?>" method="post">
    <h3><?php _e( 'Your Lists', 'erp-email-campaign' ); ?></h3>
    <ul style="list-style-type: none;">

        <?php if ( is_array( $campaign_lists ) && !empty( $campaign_lists['contact_groups'] ) ): ?>
            <?php foreach ( $campaign_lists['contact_groups'] as $list ): ?>
                <li>
                    <label>
                        <input type="checkbox" name="contact_groups[]" value="<?php echo $list['id']; ?>" <?php echo ( !in_array( $list['id'] , $unsub_groups['contact_groups'] ) ? 'checked' : '' ); ?>>
                        <?php echo esc_html( $list['title'] ); ?>
                    </label>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ( is_array( $campaign_lists ) && !empty( $campaign_lists['save_searches'] ) ): ?>
            <?php foreach ( $campaign_lists['save_searches'] as $list ): ?>
                <li>
                    <label>
                        <input type="checkbox" name="save_searches[]" value="<?php echo $list['id']; ?>" <?php echo ( !in_array( $list['id'] , $unsub_groups['save_searches'] ) ? 'checked' : '' ); ?>>
                        <?php echo esc_html( $list['title'] ); ?>
                    </label>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>

    </ul>

    <button type="submit" name="update_subscription"><?php _e( 'Update Subscription', 'erp-email-campaign' ); ?></button>

    <?php wp_nonce_field( 'ecamp-edit-subscription', '_wpnonce' ); ?>
</form>
