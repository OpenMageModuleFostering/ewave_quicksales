<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Model_Resource_Listing_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('quicksales/listing_product');
    }

}
