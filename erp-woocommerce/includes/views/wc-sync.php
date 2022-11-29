<style>
    .erp-wc-data-sync .regen-sync-response span {
        color: #8a6d3b;
        background-color: #fcf8e3;
        border-color: #faebcc;
        padding: 15px;
        margin: 10px 0;
        border: 1px solid transparent;
        border-radius: 4px;
        display: block;
    }
    .erp-wc-data-sync .regen-sync-loader {
        background: url('<?php echo admin_url( 'images/spinner-2x.gif') ?>') no-repeat;
        width: 20px;
        height: 20px;
        display: inline-block;
        background-size: cover;
    }

    .erp-wc-data-sync .postbox h3 {
        margin: 0;
    }

    .erp-wc-data-sync #progressbar {
        background-color: #EEE;
        border-radius: 13px; /* (height of inner div) / 2 + padding */
        padding: 3px;
        margin-bottom : 20px;
    }

    .erp-wc-data-sync #regen-pro {
        background-color: #00A0D2;
        width: 0%; /* Adjust with JavaScript */
        height: 20px;
        border-radius: 10px;
        text-align: center;
        color:#FFF;
    }
</style>
<div class="metabox-holder erp-wc-data-sync">
    <div class="postbox">
        <h3><?php _e( 'WooCommerce data Synchronization', 'erp-woocommerce' ); ?></h3>
        <div class="inside">
            <p><?php _e( 'This synchronize process sync your existing WooCommerce order with ERP WooCommerce related data.', 'erp-woocommerce' ); ?></p>
            <p><?php _e( 'Don\'t worry, any existing orders will not be deleted.', 'erp-woocommerce' ); ?></p>
            <p><?php _e( 'Don\'t close this window, until the process has been completed', 'erp-woocommerce' ); ?></p>

            <div class="regen-sync-response"></div>
            <div id="progressbar" style="display: none"><div id="regen-pro">0</div></div>

            <div id="regen-sync-table">
                <input type="hidden" name="limit" value="<?php echo apply_filters( 'erp_wc_sync_table_limit', 100 ); ?>">
                <input type="hidden" name="offset" value="0">

                <input id="btn-rebuild" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Synchronize Data', 'erp-woocommerce' ); ?>" >
                <span class="regen-sync-loader" style="display:none"></span>
            </div>
        </div>
    </div>
</div>
