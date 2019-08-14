<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 11.01.12
 * Time: 10:39
 * To change this template use File | Settings | File Templates.
 */
class Ewave_Quicksales_Block_RW_Adminhtml_Sales_Order_View_Tab_Info extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    public function getPaymentHtml()
    {
        if ($this->getOrder()->getQhash()) {
            echo 1;die;
            //$this->setChild(,)
        } else {
            return parent::getPaymentHtml();
        }
    }


}