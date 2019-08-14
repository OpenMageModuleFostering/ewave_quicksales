<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 11.01.12
 * Time: 10:39
 * To change this template use File | Settings | File Templates.
 */
class Ewave_Quicksales_Block_RW_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function __construct()
    {
        parent::__construct();

        $order = $this->getOrder();

        if ($order->getQhash() && $order->getStatus() == 'processing') {
            $this->_addButton('order_qpaid', array(
                'label' => Mage::helper('quicksales')->__('Mark as Paid'),
                'onclick' => 'setLocation(\'' . $this->getQpaidUrl($order) . '\')',
                'class' => 'go'
            ));
        }
    }

    public function getQpaidUrl($order)
    {
        return $this->getUrl('quicksales/adminhtml_order/qpaid', array('id' => $order->getId()));
    }

}