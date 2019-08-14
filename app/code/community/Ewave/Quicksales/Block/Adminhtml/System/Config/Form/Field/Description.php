<?php

class Ewave_Quicksales_Block_Adminhtml_System_Config_Form_Field_Description extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
//        $element->setConfig(new Varien_Object(array('enabled' => true)));
        $element->setWysiwyg(true);
        return "<script type='text/javascript' src='" . Mage::getBaseUrl('js') . "mage/adminhtml/wysiwyg/tiny_mce/setup.js'></script><script type='text/javascript' src='" . Mage::getBaseUrl('js') . "tiny_mce/tiny_mce.js'></script>" . parent::render($element);
    }

}
 
