<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 14:01
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Model_Resource_Listing_Associated_Attribute_Value_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract //Ewave_Quicksales_Model_Mysql4_Listing_Collection
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('quicksales/listing_associated_attribute_value');
    }

}
