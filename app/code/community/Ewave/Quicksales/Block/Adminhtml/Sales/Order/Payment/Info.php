<?php
/**
 */

class Ewave_Quicksales_Block_Adminhtml_Sales_Order_Payment_Info extends Mage_Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('quicksales/sales/order/payment/info.phtml');
        parent::__construct();
    }

    public function getOrder(){
        return $this->getParentBlock()->getOrder();
    }

}