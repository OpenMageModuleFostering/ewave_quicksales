<?php

class Ewave_Quicksales_Model_System_Customer_Import extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
              'guest' => 'Guest Customer'
            , 'import' => 'Imported Customer'
            , 'predefined' => 'Predefined Customer'
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
