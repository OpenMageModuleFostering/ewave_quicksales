<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function qpaidAction()
    {
        try {
            $orderId = $this->getRequest()->getParam('id');
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order && $order->getQhash()) {
                Mage::getModel('quicksales/observer')->paidOrder($order);
                $order->setState('qpaid', true);
                $order->save();

                $this->_getSession()->addSuccess(
                    $this->__('The order status has been changed to "QS Paid" successfully.')
                );
            } else {
                throw new Exception($this->__('This order is now available for QS synchronization'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('adminhtml/sales_order/view',
            array(
                'order_id' => $orderId
            )
        );
    }

}
