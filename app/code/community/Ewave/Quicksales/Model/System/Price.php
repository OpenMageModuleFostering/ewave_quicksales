<?php

class Ewave_Quicksales_Model_System_Price extends Mage_Core_Model_Abstract
{

    protected $_input = array(
        '' => '',
        'price' => 'Product Price',
        'special_price' => 'Special Price',
        'custom_price' => 'Custom Price',
    );

    public function toOptionArray()
    {


        $array = array();
        foreach ($this->_input as $key => $value) {
            $array[] = array(
                'value' => $key,
                'label' => $value,
            );
        }

        return $array;
    }

    public function getOptionsHash() {
        return $this->_input;
    }


}
