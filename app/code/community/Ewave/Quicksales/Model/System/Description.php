<?php

class Ewave_Quicksales_Model_System_Description extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
              'description' => 'Description'
            , 'short_description' => 'Short Description'
            , 'custom' => 'Custom Description'
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
