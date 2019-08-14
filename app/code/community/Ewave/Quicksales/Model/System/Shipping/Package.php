<?php

class Ewave_Quicksales_Model_System_Shipping_Package extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
              'Parcel' => 'Parcel'
            , 'Satchel' => 'Satchel'
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
