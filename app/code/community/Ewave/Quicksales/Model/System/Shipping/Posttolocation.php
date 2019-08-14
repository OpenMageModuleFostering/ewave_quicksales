<?php

class Ewave_Quicksales_Model_System_Shipping_Posttolocation extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        $input = array(
            'Worldwide' => 'Worldwide',
            'National' => 'National',
            'Statewide' => 'Statewide',
            'LocalPickup' => 'LocalPickup'


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
