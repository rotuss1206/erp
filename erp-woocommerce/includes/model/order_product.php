<?php
namespace WeDevs\ERP\WooCommerce\Model;

use WeDevs\ERP\Framework\Model;

class Order_Product extends Model {

    protected $primaryKey = 'id';
    protected $table      = 'erp_wc_order_product';
    public $timestamps    = false;
    protected $fillable   = [ 'order_id', 'product_id' ];
}
