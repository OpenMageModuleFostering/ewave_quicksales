<?php

class Ewave_Quicksales_Model_Api_Action extends Mage_Core_Model_Abstract
{

    protected $_is_sand = false;

    protected $_test_mode = false;

    protected $_added = 0;
    protected $_updated = 0;
    protected $_errors = 0;


    protected $_quiet = false;

    protected $_api = null;

    protected function _construct() {
        parent::_construct();
        $this->_is_sand = Mage::getStoreConfig('quicksales/settings/sandbox');
        $this->_api = Mage::getModel('quicksales/api');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    public function setQuiet($flag) {
        $this->_quiet = $flag;
        $this->_api->setQuiet($flag);
        return $this;
    }

    public function send($listing, $end_item = null, $productId = null, $relist = null)
    {

        $api = $this->_api;

        $message = '';

        $listing_log = Mage::getModel('quicksales/listing_log');

        $date = Zend_Date::now()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $listing_log
            ->setListingId($listing->getId())
            ->setDate($date);
        $listing_log->save();

        $message .= '<b>Magento ListingID: ' . $listing->getId() . '</b><br />';
        $result = 1;

        $listing->getData('assigned_products');

        $collection = $listing->getAssignedProducts();
        $collection->joinField('qlisting_id',
            'quicksales/listing_product',
            'quicksale_listing_id',
            'product_id=entity_id',
            '{{table}}.listing_id="' . $listing->getId() . '"',
            'inner');
        $collection->joinField('listing_product_assign_id',
            'quicksales/listing_product',
            'id',
            'product_id=entity_id',
            '{{table}}.listing_id="' . $listing->getId() . '"',
            'inner');

        $sellerId = Mage::getStoreConfig('quicksales/settings/vshop_seller');
        $sellerPwd = Mage::getStoreConfig('quicksales/settings/seller_password');

        foreach ($collection as $product) {

            if ($productId != null && $productId != $product->getProductId() && $productId != $product->getId()) {
                continue;
            }

            $Item = new Varien_Simplexml_Element('<ReviseItemRequest></ReviseItemRequest>');
            $xmlItem = new Varien_Simplexml_Config($Item);
            $xmlItem->setNode('Item/ListingID', $product->getQlistingId());


            if ($sellerId && $sellerPwd) {
                $xmlItem->setNode('Item/SellerID', $sellerId);
                $xmlItem->setNode('Item/SellerPwd', $sellerPwd);
            }

            if ($end_item !== null) {
                $xmlItem->setNode('EndItem', $end_item);
            } elseif ($relist) {

                if (!$listing->getDefaultListingConf()) {
                    $listingConfiguration = $listing->getListingInformation();
                } else {
                    $listingConfiguration = Mage::getStoreConfig('quicksales_default/listing');
                }

                $listingInformation['auto_relist'] = $listingConfiguration['auto_relist'];

                if ($listingInformation['auto_relist'] == 2) {
                    $xmlItem->setNode('RelistItem', 1);
                } else {
                    $xmlItem->setNode('RelistItem', 2);
                }

            } else {
                return false;
            }

            $result = $api->ReviseItem($xmlItem);

            if ($result instanceof Varien_Simplexml_Config) {
                $resultInformation = $result->getNode()->asArray();
                if (!$resultInformation['ListingID']) {
                    $this->_errors++;
                    $result = 0;
                } else {
                    $this->_updated++;
                    $result = 1;
                }

                $message .= $resultInformation['Message'];
            } else {
                $this->_errors++;
                $result = 0;
            }

            $date = strtotime($api->getOnDate());

            $listing_product_log = Mage::getModel('quicksales/listing_product_log');
            $listing_product_log
                ->setAssociationId($product->getListingProductAssignId())
                ->setDate($date)
                ->setMessage($message)
                ->setListingLogId($listing_log->getId());

            $listing_product_log->save();
        }


        $message .= '
        <br />
            Updated: ' . $this->_updated . '<br />
            Errors: ' . $this->_errors . '<br />
        ';

        $listing_log
            ->setResult($result)
            ->setMessage($message);

        $listing_log->save();

        if (!$this->_quiet) {
            $this->_getSession()->addNotice($message . 'Show detailed log: <a href="' . Mage::helper('adminhtml')->getUrl('*/*/productlog', array('id' => $listing_log->getId())) . '">Details</a>');
        }

    }

}

?>
