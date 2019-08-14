<?php

class Ewave_Quicksales_Model_System_Custom extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
//            ->addFieldToFilter('is_visible_on_front', 1)
        ;

        $array = array();
        foreach ($collection->getItems() as $item) {
            $array[] = array(
            	'value' => $item->getAttributeCode(),
                'label' => $item->getFrontendLabel(),
            );
        }

        return $array;
    }


}
