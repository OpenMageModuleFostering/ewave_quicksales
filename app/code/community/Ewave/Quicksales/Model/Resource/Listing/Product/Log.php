<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 27.09.11
 * Time: 17:44
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Model_Resource_Listing_Product_Log extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('quicksales/listing_product_log', 'id');
    }
}
