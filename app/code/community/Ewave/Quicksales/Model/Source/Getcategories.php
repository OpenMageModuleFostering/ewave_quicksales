<?php

class Ewave_Quicksales_Model_Source_Getcategories extends Mage_Eav_Model_Entity_Attribute_Source_Table {

    public function getAllOptions($withEmpty = true, $defaultValues = false, $parentId = 0)
    {

        /*
        $categories = Mage::getModel('quicksales/api_getcategories')->getAllCategories();
        $result = array();

        foreach ($categories as $category) {

            if ($category['parent'] == $parentId) {
                $result[] = $category;
            }
        }
        */

        $result = Mage::getModel('quicksales/api_getcategories')->getAllOptions($withEmpty, $defaultValues, $parentId);
        return $result;
    }

}

