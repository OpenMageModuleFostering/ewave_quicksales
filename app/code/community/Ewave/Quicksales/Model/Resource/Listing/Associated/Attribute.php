<?php


class Ewave_Quicksales_Model_Resource_Listing_Associated_Attribute extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('quicksales/listing_attribute', 'attribute_map_id');
    }
}

