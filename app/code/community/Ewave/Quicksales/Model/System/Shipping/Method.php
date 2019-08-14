<?php

class Ewave_Quicksales_Model_System_Shipping_Method extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
             '7' => 'Temando'
            , '1' => 'Free postage'
            , '2' => 'Flat postage'
            , '3' => 'Flat postage by location'
            , '4' => 'Calculated postage'
            , '5' => 'See item description'
            , '6' => 'No postage - local pickup only'
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
