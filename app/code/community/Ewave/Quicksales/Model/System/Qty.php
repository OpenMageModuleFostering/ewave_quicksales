<?php

class Ewave_Quicksales_Model_System_Qty extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
              'qty' => 'Quantity'
            , 'custom' => 'Custom Quantity Attribute'
        );

        $array = array();
        foreach ($input as $key => $value) {
            $array[] = array(
            	'value' => $key,
                'label' => $value,
            );
        }

        return $array;
    }


}
