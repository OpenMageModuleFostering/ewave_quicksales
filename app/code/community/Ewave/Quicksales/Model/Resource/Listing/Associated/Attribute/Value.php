<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 27.09.11
 * Time: 17:44
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Model_Resource_Listing_Associated_Attribute_Value extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('quicksales/listing_attribute_value', 'attribute_value_map_id');
    }
}
