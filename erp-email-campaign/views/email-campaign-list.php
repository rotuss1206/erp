<div class="wrap erp-email-campaign erp-email-campaign-list" id="erp-email-campaign-list">
    <h2><?php _e( 'Email Campaigns', 'erp-email-campaign' ); ?>
        <?php
            $slug =  array();
            if ( version_compare( WPERP_VERSION, '1.4.0', '>=' ) ) {
               $slug['sub-section'] = 'email-campaign-editor';
            }

        ?>
        <a href="<?php echo ecamp_admin_url( $slug, 'erp-email-campaign-editor' ); ?>" id="erp-customer-new" class="erp-campaign-new add-new-h2" data-type="campaign" title="<?php _e( 'Create New Campaign', 'erp-email-campaign' ); ?>">
            <?php _e( 'Add New', 'erp-email-campaign' ); ?>
        </a>
    </h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">

            <form method="get" class="email-campaign-list-table-form">
                <?php if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ): ?>
                    <input type="hidden" name="page" value="erp-email-campaign">
                <?php else: ?>
                    <input type="hidden" name="page" value="erp-crm">
                    <input type="hidden" name="section" value="email-campaign">
                <?php endif?>
                <?php
                    $campaign_table->prepare_items();
                    $campaign_table->search_box();
                    $campaign_table->views();

                    $campaign_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>
