<?php

class Ewave_Quicksales_Model_Source_Vshopcategory extends Mage_Eav_Model_Entity_Attribute_Source_Table
{

    public function getAllOptions()
    {
        if (!$this->_options) {

            $options = array(
                array('value'=> '', 'label' => '')
            );
            $suboptions = array();

            $vshopCategories = Mage::getModel('quicksales/api_getvshopcategory')->getVshopCategories();

            if (!$vshopCategories) {
                return false;
            }
            foreach ($vshopCategories as $vshopCategory) {

                if ($vshopCategory['leaf'] == "True") {
                    
                        $option = &$suboptions[$vshopCategory['parent']];

                        $option[$vshopCategory['id']] = array(
                            'value' => $vshopCategory['id'],
                            'label' => $vshopCategory['name']
                        );

                } else {

                    if ($vshopCategory['parent'] == -1) {
                        $options[$vshopCategory['id']] = array(
                            'value' => array(),
                            'label' => ''//$vshopCategory['name']
                        );

                        $suboptions[$vshopCategory['id']] = &$options[$vshopCategory['id']]['value'];
                    } else {
                        $option = &$suboptions[$vshopCategory['parent']];

                        $option[$vshopCategory['id']] = array(
                            'value' => array(),
                            'label' => $vshopCategory['name']
                        );

                        $suboptions[$vshopCategory['id']] = &$option[$vshopCategory['id']]['value'];
                    }


                }
            }

            $this->_options = $options;
        }

        return $this->_options;
    }

}
 
