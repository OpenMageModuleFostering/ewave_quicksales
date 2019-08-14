<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 27.09.11
 * Time: 17:42
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Model_Listing_Product_Log extends Mage_Core_Model_Abstract {

    public function _construct()
    {
        parent::_construct();
        $this->_init('quicksales/listing_product_log');
    }

}
