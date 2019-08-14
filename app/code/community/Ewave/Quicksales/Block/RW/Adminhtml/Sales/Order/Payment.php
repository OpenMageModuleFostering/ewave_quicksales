<?php
/**
 */

class Ewave_Quicksales_Block_RW_Adminhtml_Sales_Order_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment {


    public function setPayment($payment)
    {
        $order = $this->getParentBlock()->getOrder();

        if ($order->getQhash()) {
            $this->setOrder($order);
            $paymentInfoBlock = $this->getLayout()->createBlock('quicksales/adminhtml_sales_order_payment_info');
        } else {
            $paymentInfoBlock = Mage::helper('payment')->getInfoBlock($payment);
        }
        $this->setChild('info', $paymentInfoBlock);
        $this->setData('payment', $payment);
        return $this;
    }

    protected function _toHtml()
    {
        return $this->getChildHtml('info', false);
    }

}
