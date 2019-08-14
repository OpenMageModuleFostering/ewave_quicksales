<?php

class Ewave_Quicksales_Block_Adminhtml_System_Config_Form_Field_Datetime extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    public function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {


        $calendar = $this->getLayout()
                ->createBlock('core/html_date')
                ->setId('options_' . $element->getId())
                ->setName($element->getName())
                ->setClass('product-custom-option datetime-picker input-text')
                ->setImage($this->getSkinUrl('images/grid-cal.gif'))
                ->setFormat('%d/%m/%Y %H:%M:%S')
                ->setValue($element->getValue())
        ;

        return $calendar->getHtml();
    }

}
 
