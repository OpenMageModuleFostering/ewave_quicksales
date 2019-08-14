<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 16:02
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Model_Mysql4_Listing extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('quicksales/listing', 'listing_id');
    }
}
