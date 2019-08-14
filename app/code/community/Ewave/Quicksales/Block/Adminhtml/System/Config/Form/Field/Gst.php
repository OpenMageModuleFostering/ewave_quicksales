<?php

class Ewave_Quicksales_Block_Adminhtml_System_Config_Form_Field_Gst extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
//        $element->setConfig(new Varien_Object(array('enabled' => true)));
        $element->setText('
        <p>
            <a target="_blank" href="http://www.quicksales.com.au/content/0012005/help/Sell/listingpolicies.aspx">
                http://www.quicksales.com.au/content/0012005/help/Sell/listingpolicies.aspx
            </a>
        </p>

        <p>
            GST: All prices are to be shown in Australian dollars and unless otherwise clearly stated, all prices are GST inclusive. GST can only be added to the final price if the payment instructions CLEARLY state that GST will be added to the final price. Note: Only sellers that are GST registered can charge GST.
        </p>

        <p>
            <a target="_blank" href="http://www.quicksales.com.au/content/0012005/help/MyAccount/gst.aspx">
                GST info
            </a>
        </p>
    ');
        return parent::render($element);
    }

}
 
