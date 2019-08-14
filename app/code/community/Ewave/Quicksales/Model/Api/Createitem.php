<?php

class Ewave_Quicksales_Model_Api_Createitem extends Mage_Core_Model_Abstract
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

    public function send($listing, $productId = null)
    {
        $message = '';
        $result = 1;

        $api = $this->_api;

        if ($this->_test_mode) {

            $result = $api->CreateItem();
            if ($result instanceof Varien_Simplexml_Config) {
                print_r($result->getNode()->asArray());
            } else {
                echo $result;
            }
            echo  $api->getOnDate() . '<br />';
            echo date('Y-m-d h:i:s', strtotime($api->getOnDate()));
            die();
        }

        $listing_log = Mage::getModel('quicksales/listing_log');

        $date = time();
        $listing_log
            ->setListingId($listing->getId())
            ->setDate($date);
        $listing_log->save();

        $listing->getData('assigned_products');

        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addIdFilter($listing->getData('assigned_products'));

        $collection->addAttributeToSelect('*');

        $collection->joinField('qty',
            'cataloginventory/stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'inner');

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

        $availableAttributes = $listing->getAttributesAssociation();
        $assignedAttributes = array();
        if (!empty($availableAttributes)) {
            $attribute = Mage::getModel('eav/entity_attribute');
            foreach ($availableAttributes as $qAttributeId => $mAttributeId) {
                $attribute->load($mAttributeId);
                //                $collection->addAttributeToSelect($attribute->getAttributeCode());

                $assignedAttributes[$qAttributeId] = $attribute->getAttributeCode();
            }
        }

        $availableValues = $listing->getAttributeValuesAssociation();

        $sellerId = Mage::getStoreConfig('quicksales/settings/vshop_seller');
        $sellerPwd = Mage::getStoreConfig('quicksales/settings/seller_password');

        $attrSet = Mage::getModel('quicksales/api_gettags')->getAttrSet($listing->getCategory());

        $message .= '<b>Magento ListingID: ' . $listing->getId() . '</b><br />';

        if (count($collection) == 0 && !$this->_quiet) {
            throw new Exception('Please select a product');
        }

        foreach ($collection as $product) {

            try {

                if ($productId != null && $product->getId() != $productId) {
                    continue;
                }

                if ($product->getQlistingId()) {
                    $Item = new Varien_Simplexml_Element('<ReviseItemRequest></ReviseItemRequest>');
                    $xmlItem = new Varien_Simplexml_Config($Item);
                    $xmlItem->setNode('Item/ListingID', $product->getQlistingId());

                } else {
                    $Item = new Varien_Simplexml_Element('<CreateItemRequest></CreateItemRequest>');
                    $xmlItem = new Varien_Simplexml_Config($Item);
                }

                $stopListing = false;

                if (!$listing->getDefaultListingConf()) {
                    $listingConfiguration = $listing->getListingInformation();
                } else {
                    $listingConfiguration = Mage::getStoreConfig('quicksales_default/listing');
                }

                $listingInformation = array();


                if ($listingConfiguration['qty'] == 'custom') {
                    $listingInformation['qty'] = $listingConfiguration['qty_custom'];
                } else {
                    $listingInformation['qty'] = $listingConfiguration['qty'];
                }

                $quantity = (int)$product->getData($listingInformation['qty']);

                if ($quantity == 0) {
                    if ($product->getQlistingId()) {
                        $stopListing = true;
                    } else {
                        throw(new Exception('Invalid Quantity. Quantity must be between 1 to 999 , product: ' . $product->getName() . ', selected quantity: ' . $quantity));
                    }
                }


                if (!$stopListing) {

                    $xmlItem->setNode('Item/Title', $product->getName());

                    $xmlItem->setNode('Item/Category', $listing->getCategory());

                    if ($sellerId && $sellerPwd) {
                        $xmlItem->setNode('Item/SellerID', $sellerId);
                        $xmlItem->setNode('Item/SellerPwd', $sellerPwd);
                    }

                    if ($listing->getVshopCategory() && $listing->getVshopCategory() != 1) {
                        $xmlItem->setNode('Item/vShopCategory', $listing->getVshopCategory());
                    }

                    if (!empty($assignedAttributes) && !empty($availableValues)) {
                        $attributeCounter = 0;
                        foreach ($attrSet as $k => $setInfo) {
                            if ($k == '@' && !empty($setInfo)) {
                            } else {

                                if (
                                    !($product->hasData($assignedAttributes[$setInfo['@']['AttrID']]))
                                    || !$availableValues[$setInfo['@']['AttrID']][$product->getData($assignedAttributes[$setInfo['@']['AttrID']])]
                                ) {
                                    continue;
                                }
                                $attributeCounter++;
                            }
                        }
                        if ($attributeCounter > 0) {

                            $xmlItem->setNode('Item/Tags/AttrSet', "");

                            $AttrSetTag = $xmlItem->getNode('Item/Tags/AttrSet');
                            $attributeCounter = 0;
                            foreach ($attrSet as $k => $setInfo) {
                                if ($k == '@' && !empty($setInfo)) {

                                    foreach ($setInfo as $code => $value) {
                                        $AttrSetTag->addAttribute($code, $value);
                                    }
                                } else {

                                    if (
                                        !($product->hasData($assignedAttributes[$setInfo['@']['AttrID']]))
                                        || !$availableValues[$setInfo['@']['AttrID']][$product->getData($assignedAttributes[$setInfo['@']['AttrID']])]
                                    ) {
                                        continue;
                                    }
                                    $attributeCounter++;
                                    $xmlItem->setNode('Item/Tags/AttrSet/Attr' . $attributeCounter, "");
                                    $AttrTag = $xmlItem->getNode('Item/Tags/AttrSet/Attr' . $attributeCounter);

                                    $xmlItem->setNode('Item/Tags/AttrSet/Attr' . $attributeCounter . '/SelectedValue', "");
                                    $ValueTag = $xmlItem->getNode('Item/Tags/AttrSet/Attr' . $attributeCounter . '/SelectedValue');

                                    foreach ($setInfo['@'] as $kk => $attributeInfo) {
                                        $AttrTag->addAttribute($kk, $attributeInfo);
                                        if ($kk == 'AttrID') {

                                            if (!empty($assignedAttributes) && $assignedAttributes[$attributeInfo]) {

                                                $mValueId = $product->getData($assignedAttributes[$attributeInfo]);
                                                $ValueTag
                                                    ->addAttribute('ValueID', $availableValues[$setInfo['@']['AttrID']][$mValueId]);

                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }

                    // Pricing
                    if (!$listing->getDefaultPricingConf()) {
                        $pricingConfiguration = $listing->getPricingInformation();
                    } else {
                        $pricingConfiguration = Mage::getStoreConfig('quicksales_default/pricing');
                    }

                    $pricingInformation = array();

                    if ($pricingConfiguration['auction_start_price'] == 'custom_price') {
                        $pricingInformation['auction_start_price'] = $pricingConfiguration['auction_start_price_custom'];
                    } else {
                        $pricingInformation['auction_start_price'] = $pricingConfiguration['auction_start_price'];
                    }

                    if ($pricingConfiguration['buy_now_price'] == 'custom_price') {
                        $pricingInformation['buy_now_price'] = $pricingConfiguration['auction_start_price_custom'];
                    } else {
                        $pricingInformation['buy_now_price'] = $pricingConfiguration['buy_now_price'];
                    }

                    $pricingInformation['bid_increment'] = $pricingConfiguration['bid_increment'];

                    $buy_now_price = 0;
                    $buyNowOnly = false;

                    if ($product->getData($pricingInformation['buy_now_price'])) {
                        $buy_now_price = $product->getData($pricingInformation['buy_now_price']);
                        $buy_now_price = number_format($buy_now_price, 2, '.', '');

                        $xmlItem->setNode('Item/BuyNowPrice', $buy_now_price);

                        if ($product->getData($pricingInformation['auction_start_price']) && $product->getData($pricingInformation['auction_start_price']) < $product->getData($pricingInformation['buy_now_price']) && $pricingInformation['bid_increment']) {
                            $xmlItem->setNode('Item/StartPrice', (float)$product->getData($pricingInformation['auction_start_price']));
                            $xmlItem->setNode('Item/BidIncrement', (float)$pricingInformation['bid_increment']);
                        } else {
                            $buyNowOnly = true;
                        }

                    } else {
                        if ($product->getData($pricingInformation['auction_start_price']) && $pricingInformation['bid_increment']) {
                            $xmlItem->setNode('Item/StartPrice', (float)$product->getData($pricingInformation['auction_start_price']));
                            $xmlItem->setNode('Item/BidIncrement', (float)$pricingInformation['bid_increment']);
                        }
                    }
                    // End Pricing


                    //Listing
                    if (!$listing->getDefaultListingConf()) {
                        $listingConfiguration = $listing->getListingInformation();
                    } else {
                        $listingConfiguration = Mage::getStoreConfig('quicksales_default/listing');
                    }

                    $listingInformation = array();


                    if ($listingConfiguration['description'] == 'custom') {
                        $listingInformation['custom_description'] = $listingConfiguration['description_custom'];
                    } else {
                        $listingInformation['description'] = $listingConfiguration['description'];
                    }

                    $listingInformation['duration'] = $listingConfiguration['duration'];


                    $listingInformation['start_date'] = $listingConfiguration['start_date'];

                    $listingInformation['brand_new'] = $listingConfiguration['brand_new'];
                    $listingInformation['auto_relist'] = $listingConfiguration['auto_relist'];


                    if ($listingConfiguration['qty'] == 'custom') {
                        $listingInformation['qty'] = $listingConfiguration['qty_custom'];
                    } else {
                        $listingInformation['qty'] = $listingConfiguration['qty'];
                    }

                    if (!$buyNowOnly) {
                        $listingInformation['bid_now'] = $listingConfiguration['bid_now'];
                    }

                    $listingInformation['auto_1min'] = $listingConfiguration['auto_1min'];

                    $xmlItem->setNode('Item/Description', ($listingInformation['description']
                        ? $product->getData($listingInformation['description'])
                        : $listingInformation['custom_description']));

                    if (!$buyNowOnly) {
                        $xmlItem->setNode('Item/Duration', $listingInformation['duration']);
                    }

                    if ($listingInformation['start_date']) {
                        $xmlItem->setNode('Item/StartTime', $listingInformation['start_date']);
                    }

                    $xmlItem->setNode('Item/BrandNew', $listingInformation['brand_new']);
                    $xmlItem->setNode('Item/Quantity', (int)$product->getData($listingInformation['qty']));

                    $xmlItem->setNode('Item/PromotionalFeatures/BidNow', $listingInformation['bid_now']);
                    $xmlItem->setNode('Item/PromotionalFeatures/Auto1Minute', $listingInformation['auto_1min']);

                    if (!empty($listingInformation['auto_relist'])) {
                        $xmlItem->setNode('Item/AutoRelistFeatures/AutoRelistType', $listingInformation['auto_relist']);
                    }

                    // End Listing

                    // Listing Upgrade
                    if (!$listing->getDefaultListingUpgradeConf()) {
                        $listingUpgradeConfiguration = $listing->getListingUpgradeInformation();
                    } else {
                        $listingUpgradeConfiguration = Mage::getStoreConfig('quicksales_default/listing_upgrade');
                    }

                    $listingUpgradeInformation = array();

                    $listingUpgradeInformation = $listingUpgradeConfiguration;


                    $xmlItem->setNode('Item/PromotionalFeatures/Frontpage', $listingUpgradeInformation['front_page_featured']);
                    $xmlItem->setNode('Item/PromotionalFeatures/CategorySpecial', $listingUpgradeInformation['category_special']);

                    $xmlItem->setNode('Item/PromotionalFeatures/HighlightListing', $listingUpgradeInformation['highlight_listing']);

                    $xmlItem->setNode('Item/PromotionalFeatures/Bold', $listingUpgradeInformation['bold_title']);

                    // End Listing Upgrade

                    // Payment
                    if (!$listing->getDefaultPaymentConf()) {
                        $PaymentConfiguration = $listing->getPaymentInformation();
                    } else {
                        $PaymentConfiguration = Mage::getStoreConfig('quicksales_default/payment');
                    }

                    $PaymentInformation = array();

                    $PaymentInformation = $PaymentConfiguration;

                    if (!is_array($PaymentInformation['method'])) {
                        $PaymentInformation['method'] = explode(',', $PaymentInformation['method']);
                    }

                    $payments = Mage::getModel('quicksales/source_payments')->getPayments();

                    foreach ($payments as $payment) {
                        $enable = (int)in_array($payment, $PaymentInformation['method']);
                        $xmlItem->setNode('Item/PaymentMethods/' . $payment, $enable);
                    }
                    /*
                    if (!empty($PaymentInformation['method'])) {
                        foreach ($PaymentInformation['method'] as $method) {
                            $xmlItem->setNode('Item/PaymentMethods/' . $method, '1');
                        }
                    }
                    */

                    $xmlItem->setNode('Item/ProvideReturnRefundPolicy', $PaymentInformation['show_policy']);


                    if (!empty($PaymentInformation['instruction'])) {
                        $xmlItem->setNode('Item/PaymentInst', $PaymentInformation['instruction']);
                    }

                    // End Payment

                    // Shipping
                    if (!$listing->getDefaultShippingConf()) {
                        $ShippingConfiguration = $listing->getShippingInformation();
                    } else {
                        $ShippingConfiguration = Mage::getStoreConfig('quicksales_default/shipping');
                    }

                    $ShippingInformation = array();

                    $ShippingInformation['Postage/Type'] = $ShippingConfiguration['method'];

                    if ($ShippingConfiguration['method'] == '2') {

                        if ($ShippingConfiguration['flat_rate'])
                            $ShippingInformation['Postage/FixedPostage'] = (float)$ShippingConfiguration['flat_rate'];
                    } elseif ($ShippingConfiguration['method'] == '3') {

                        $ShippingInformation['Postage/FixedPostageByLocation/NSW'] = (float)$ShippingConfiguration['flat_table_rate_nsw'];
                        $ShippingInformation['Postage/FixedPostageByLocation/VIC'] = (float)$ShippingConfiguration['flat_table_rate_vic'];
                        $ShippingInformation['Postage/FixedPostageByLocation/QLD'] = (float)$ShippingConfiguration['flat_table_rate_qld'];
                        $ShippingInformation['Postage/FixedPostageByLocation/ACT'] = (float)$ShippingConfiguration['flat_table_rate_act'];
                        $ShippingInformation['Postage/FixedPostageByLocation/NT'] = (float)$ShippingConfiguration['flat_table_rate_nt'];
                        $ShippingInformation['Postage/FixedPostageByLocation/TAS'] = (float)$ShippingConfiguration['flat_table_rate_tas'];
                        $ShippingInformation['Postage/FixedPostageByLocation/SA'] = (float)$ShippingConfiguration['flat_table_rate_sa'];
                        $ShippingInformation['Postage/FixedPostageByLocation/WA'] = (float)$ShippingConfiguration['flat_table_rate_wa'];

                    } elseif ($ShippingConfiguration['method'] == '4') {

                        $ShippingInformation['Postage/CalculatedPostage/OfferAPRegular'] = $ShippingConfiguration['au_post_regular'];

                        $ShippingInformation['Postage/CalculatedPostage/OfferAPExpress'] = $ShippingConfiguration['calc_au_post_express'];

                        $ShippingInformation['Postage/CalculatedPostage/Dimensions/Length'] = $product->getData($ShippingConfiguration['calc_length']);
                        $ShippingInformation['Postage/CalculatedPostage/Dimensions/Width'] = $product->getData($ShippingConfiguration['calc_width']);
                        $ShippingInformation['Postage/CalculatedPostage/Dimensions/Height'] = $product->getData($ShippingConfiguration['calc_height']);
                        $ShippingInformation['Postage/CalculatedPostage/Dimensions/Weight'] = $product->getData($ShippingConfiguration['calc_weight']);

                    } elseif ($ShippingConfiguration['method'] == '7') {

                        $ShippingInformation['Postage/Temando/PkgType'] = $ShippingConfiguration['temando_package'];

                        $ShippingInformation['Postage/Temando/Length'] = (int)$product->getData($ShippingConfiguration['temando_length']);
                        $ShippingInformation['Postage/Temando/Width'] = (int)$product->getData($ShippingConfiguration['temando_width']);
                        $ShippingInformation['Postage/Temando/Height'] = (int)$product->getData($ShippingConfiguration['temando_height']);
                        $ShippingInformation['Postage/Temando/Weight'] = (int)$product->getData($ShippingConfiguration['temando_weight']);

                    }

                    if (!is_array($ShippingConfiguration['posttolocation'])) {
                        $ShippingConfiguration['posttolocation'] = explode(',', $ShippingConfiguration['posttolocation']);
                    }

                    if (!empty($ShippingConfiguration['posttolocation'])) {
                        foreach ($ShippingConfiguration['posttolocation'] as $location) {
                            $ShippingInformation['PostToLocation/' . $location] = 1;
                        }
                    }

                    if (!empty($ShippingConfiguration['postinst'])) {
                        $ShippingInformation['PostInst'] = $ShippingConfiguration['postinst'];
                    }

                    foreach ($ShippingInformation as $node => $value) {
                        $xmlItem->setNode('Item/' . $node, $value);
                    }

                    // End Shipping

                    $images = Mage::getModel('catalog/product')->load($product->getId())->getMediaGalleryImages();

                    $counter = 0;
                    foreach ($images as $image) {
                        $counter++;
                        $xmlItem->setNode('Item/Pic' . $counter, $image->getUrl());
                        if ($counter == 3) {
                            break;
                        }
                    }

                    if ($buyNowOnly) {
                        //$xmlItem->setNode('DeleteField', 'ITEM.AUCTION_START_PRICE');
                        //$xmlItem->setNode('DeleteField', 'ITEM.BID_INCREMENT');
                        $root = $xmlItem->getNode();
                        $root->addChild('DeleteField', 'ITEM.AUCTION_START_PRICE');
                        $root->addChild('DeleteField', 'ITEM.BID_INCREMENT');

                    }

                } else {
                    $xmlItem->setNode('Item/EndItem', 1);
                }

                if ($product->getQlistingId()) {
                    $result = $api->ReviseItem($xmlItem);
                } else {
                    $result = $api->CreateItem($xmlItem);
                }

                if ($result instanceof Varien_Simplexml_Config) {
                    $resultInformation = $result->getNode()->asArray();
                    if ($resultInformation['ListingID']) {
                        $listing_product = Mage::getModel('quicksales/listing_product')->load($product->getListingProductAssignId());
                        if (!$product->getQlistingId()) {
                            $this->_added++;
                        } else {
                            $this->_updated++;
                        }

                        $listing_product->setQuicksaleListingId($resultInformation['ListingID']);
                        $listing_product->save();
                    } else {
                        $this->_errors++;
                        $result = 0;
                    }

                    $message .= $resultInformation['Message'];
                } else {
                    $this->_errors++;
                    $message .= $result;
                    $result = 0;
                }

            } catch (Exception $e) {
                $this->_errors++;
                if (!$this->_quiet) {
                    $this->_getSession()->addError($e->getMessage());
                }
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

        $message = '<b>Magento ListingID: '
            . $listing->getId()
            . '</b><br />'
            .'
            Added: ' . $this->_added . '<br />
            Updated: ' . $this->_updated . '<br />
            Errors: ' . $this->_errors . '<br />
            ';

        $listing_log
            ->setResult($result)
            ->setMessage($message);
        $listing_log->save();

        if (!$this->_quiet) {
            $this->_getSession()->addNotice($message . 'Show detailed log: <a href="' . Mage::helper('adminhtml')->getUrl('quicksales/adminhtml_listing/productlog', array('id' => $listing_log->getId())) . '">Details</a>');
        }
    }
}

?>
