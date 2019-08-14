<?php

class Ewave_Quicksales_Model_Listing_Associated_Attribute extends Mage_Core_Model_Abstract {

    public function _construct()
    {
        parent::_construct();
        $this->_init('quicksales/listing_associated_attribute');
    }

}
