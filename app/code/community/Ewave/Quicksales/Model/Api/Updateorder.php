<?php
/*
<UpdateOrderRequest>
  <UserID>string</UserID>
  <UserPwd>string</UserPwd>
  <InvoiceID>integer</InvoiceID>
  <Action>integer</Action>
  <NotifyBuyer>integer</NotifyBuyer>
</UpdateOrderRequest>
*/

class Ewave_Quicksales_Model_Api_Updateorder extends Mage_Core_Model_Abstract
{

    protected $_is_sand = false;

    protected $_test_mode = false;

    protected $_quiet = false;

    protected $_api = null;

    protected $_added = 0;
    protected $_updated = 0;
    protected $_errors = 0;


    protected function _construct()
    {
        parent::_construct();
        $this->_is_sand = Mage::getStoreConfig('quicksales/settings/sandbox');
        $this->_api = Mage::getModel('quicksales/api');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    public function setQuiet($flag)
    {
        $this->_quiet = $flag;
        $this->_api->setQuiet($flag);
        return $this;
    }

    public function updateOrder($data = null, $orderId)
    {
        $api = $this->_api;


        $listing_log = Mage::getModel('quicksales/listing_log');

        $date = Zend_Date::now()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $rez = 1;

        $listing_log
            ->setOrderId($orderId)
            ->setType(2)
            ->setDate($date);
        $listing_log->save();

        $sellerId = Mage::getStoreConfig('quicksales/settings/vshop_seller');
        $sellerPwd = Mage::getStoreConfig('quicksales/settings/seller_password');

        $Item = new Varien_Simplexml_Element('<UpdateOrderRequest></UpdateOrderRequest>');
        $xmlItem = new Varien_Simplexml_Config($Item);

        $xmlItem->setNode('UserID', $sellerId);
        $xmlItem->setNode('UserPwd', $sellerPwd);

        if (!empty($data)) {
            foreach ($data as $field => $value) {
                $xmlItem->setNode($field, $value);
            }
        }


        $result = $api->UpdateOrder($xmlItem);

        if ($result instanceof Varien_Simplexml_Config) {
            $resultInformation = $result->getNode()->asArray();
            if ($resultInformation['InvoiceID']) {

                $this->_updated++;

            } else {
                $this->_errors++;
                $rez = 0;
            }

            $message = $resultInformation['Message'];
        } else {
            $this->_errors++;
            $rez = 0;
            $message = $result;
        }

        $listing_log
            ->setMessage($message)
            ->setResult($rez)
        ;
        $listing_log->save();


        if (!$this->_quiet) {
            $this->_getSession()->addNotice($message );
        }

        return $result;

    }

    public function update($data = null, $orderId = null)
    {
        $xmlResponce = $this->updateOrder($data, $orderId);

        if (!($xmlResponce instanceof Varien_Simplexml_Config)) {
            return false;
        }

        file_put_contents('UpdateOrderResponse.xml', $xmlResponce->getXmlString());

    }

}
