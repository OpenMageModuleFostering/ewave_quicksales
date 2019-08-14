<?php

class Ewave_Quicksales_Model_Api_Getitem extends Mage_Core_Model_Abstract
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

    public function getItem($data)
    {
        $api = $this->_api;

        $listing_log = Mage::getModel('quicksales/listing_log');

        $date = time();
        $listing_log
                ->setListingId($data->getListingId())
                ->setDate($date);
        $listing_log->save();


        $sellerId = Mage::getStoreConfig('quicksales/settings/vshop_seller');
        $sellerPwd = Mage::getStoreConfig('quicksales/settings/seller_password');

        $Item = new Varien_Simplexml_Element('<GetItemRequest></GetItemRequest>');
        $xmlItem = new Varien_Simplexml_Config($Item);
        $xmlItem->setNode('ListingID', $data->getQuicksaleListingId());


        $xmlItem->setNode('SellerID', $sellerId);
        $xmlItem->setNode('SellerPwd', $sellerPwd);

        $xmlItem->setNode('Status', 'SELLING');

        $xmlItem->setNode('ReturnField', 'ITEM.QUANTITY');


        $result = $api->GetItem($xmlItem);

        if ($result instanceof Varien_Simplexml_Config) {
            $resultInformation = $result->getNode()->asArray();
            if ($resultInformation['ListingID'] || $resultInformation['Item']) {

                $this->_updated++;

            } else {
                $this->_errors++;
            }

            $message = $resultInformation['Message'];
        } else {
            $this->_errors++;
            $message = $result;
        }

        $date = strtotime($api->getOnDate());

        $listing_product_log = Mage::getModel('quicksales/listing_product_log');
        $listing_product_log
                ->setAssociationId($data->getId())
                ->setDate($date)
                ->setMessage($message)
                ->setListingLogId($listing_log->getId());

        $listing_product_log->save();

        $message = '
            Updated: ' . $this->_updated . '<br />
            Errors: ' . $this->_errors . '<br />
        ';

        $listing_log
                ->setMessage($message);
        $listing_log->save();

        if (!$this->_quiet) {
            $this->_getSession()->addNotice($message . 'Show detailed log: <a href="' . Mage::helper('adminhtml')->getUrl('quicksales/adminhtml_listing/productlog', array('id' => $listing_log->getId())) . '">Details</a>');
        }

        return $result;

    }

    public function synchronize($item)
    {
        $xmlResponce = $this->getItem($item);

        if (!($xmlResponce instanceof Varien_Simplexml_Config)) {
            return false;
        }

        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        $stockData = $product->getStockData();

        $qty = (string)$xmlResponce->getNode('Item/Quantity');

        if ($qty == $stockData['qty']) {
            return false;
        }

        $stockData['qty'] = $qty;

	    $product->setStockData($stockData);

        $product->save();
        echo '.';
    }

}

?>
