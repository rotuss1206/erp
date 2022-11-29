<?php
namespace WeDevs\ERP\WooCommerce\Model;

use WeDevs\ERP\Framework\Model;

class Product_Order extends Model {

    protected $primaryKey = 'id';
    protected $table      = 'erp_wc_orders';
    public $timestamps    = false;
    protected $fillable   = [ 'people_id', 'order_id', 'order_status', 'order_date', 'order_total', 'accounting' ];

    /**
    * name
    *
    * @since 0.0.1
    *
    * @return void
    **/
    public function order_product() {
        $this->hasMany( '\WeDevs\ERP\WooCommerce\Model\Order_Product', 'order_id', 'order_id' );
    }

}
