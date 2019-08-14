<?php

class Ewave_Quicksales_Model_Resource_Listing_Associated_Attribute_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('quicksales/listing_associated_attribute');
    }

}
