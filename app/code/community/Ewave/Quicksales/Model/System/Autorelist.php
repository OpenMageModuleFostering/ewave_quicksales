<?php

class Ewave_Quicksales_Model_System_Autorelist extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
              2 => 'Auto-Relist'
            , 3 => 'Do not Auto-Relist'
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
