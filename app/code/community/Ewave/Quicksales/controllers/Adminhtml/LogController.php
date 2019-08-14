<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Adminhtml_LogController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('quicksales/listing')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Listing'), Mage::helper('adminhtml')->__('Log'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function importordersAction() {

        Mage::getModel('quicksales/observer')->getQuicksalesOrders();
        $this->_redirect('quicksales/adminhtml_log/index');
    }
}
