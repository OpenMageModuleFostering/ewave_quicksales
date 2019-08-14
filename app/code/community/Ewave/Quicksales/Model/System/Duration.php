<?php

class Ewave_Quicksales_Model_System_Duration extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
              '3'  => '3'
            , '5'  => '5'
            , '7'  => '7'
            , '10' => '10'
            , '14' => '14'
            , '45' => '45'
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
