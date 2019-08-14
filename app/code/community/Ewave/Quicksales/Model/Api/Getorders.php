<?php

/*
<GetOrdersRequest>
  <UserID>string</UserID>
  <UserPwd>string</UserPwd>
  <NumberOfDays>integer</NumberOfDays>
  <RequestingRole>string</RequestingRole>
  <InvoiceID>integer</InvoiceID>
</GetOrdersRequest>
*/

class Ewave_Quicksales_Model_Api_Getorders extends Mage_Core_Model_Abstract
{

    protected $_is_sand = false;

    protected $_test_mode = false;

    protected $_quiet = false;

    protected $_api = null;

    protected $_added = 0;
    protected $_updated = 0;
    protected $_errors = 0;

    protected $_listing_log = null;


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

    public function getOrders($data = null)
    {
        $api = $this->_api;
        $rez = 1;

        $this->_listing_log = Mage::getModel('quicksales/listing_log');

        $date = Zend_Date::now()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $this->_listing_log
            ->setType(3)
            ->setDate($date);
        $this->_listing_log->save();

        $sellerId = Mage::getStoreConfig('quicksales/settings/vshop_seller');
        $sellerPwd = Mage::getStoreConfig('quicksales/settings/seller_password');

        $Item = new Varien_Simplexml_Element('<GetOrdersRequest></GetOrdersRequest>');
        $xmlItem = new Varien_Simplexml_Config($Item);

        $xmlItem->setNode('UserID', $sellerId);
        $xmlItem->setNode('UserPwd', $sellerPwd);

        if (!empty($data)) {
            foreach ($data as $field => $value) {
                $xmlItem->setNode($field, $value);
            }
        }

        $result = $api->GetOrders($xmlItem);

        if ($result instanceof Varien_Simplexml_Config) {

            $this->_updated++;
            $message = '';
        } else {
            $this->_errors++;
            $rez = 0;
            $message = $result;
        }

        $this->_listing_log
            ->setMessage($message)
            ->setResult($rez)
        ;
        $this->_listing_log->save();


        if (!$this->_quiet) {
            //$this->_getSession()->addNotice($message . 'Show detailed log: <a href="' . Mage::helper('adminhtml')->getUrl('quicksales/adminhtml_listing/productlog', array('id' => $this->_listing_log->getId())) . '">Details</a>');
            $this->_getSession()->addNotice($message);
        }

        return $result;

    }

    public function import($data = null)
    {
        $message = '';

        if (empty($this->_listing_log)) {
            $message = "Orders Import<br />\n";
        }

        if (!Mage::registry('quicksales_data')) {
            Mage::register('quicksales_data', 1);
        }

        if (!Mage::getStoreConfig('quicksales/order_customer/import_orders')) {
            return false;
        }
        $xmlResponce = $this->getOrders($data);

        if (!($xmlResponce instanceof Varien_Simplexml_Config)) {
            return false;
        }

        $importedOrders = array();
        $pendingOrders = array();

        $InvoiceArray = $xmlResponce->getNode('InvoiceArray');
        foreach ($InvoiceArray->Invoice as $Invoice) {

            $Transactions = $Invoice->TransactionArray;
            foreach ($Transactions->Transaction as $Transaction) {
                if ((string)$Transaction->ItemID == '') {
                    continue;
                }

                $ordersCount = 0;

                foreach ($Transactions->Transaction as $t) {
                    if ((string)$Transaction->CheckoutDetails->CheckoutID == (string)$t->CheckoutDetails->CheckoutID) {
                        $ordersCount++;
                    }
                }

                $qhash = (string)$Transaction->ItemID;
                $oldStatus = '';
                $skeepOrder = false;

                if ($qhash) {
                    $orders = Mage::getModel('sales/order')
                        ->getCollection()
                        ->addFieldToFilter('status', array('neq' => 'canceled'))
                        ->addAttributeToFilter('qhash', $qhash);

                    if ($orders->count() > 0) {
                        foreach ($orders as $order) {
                            //                        echo 'Order #' . $order->getId() . ' already imported  <br />' . "\n";
							$pendingOrders[$qhash] = $order->getId();
                            $oldStatus = $order->getStatus();
                            if ($oldStatus == 'qnot_checked_out' && ((string)$Transaction->CheckoutDetails->CheckoutID == '')) {
                                $skeepOrder = true;
                                break;
                            } elseif ($oldStatus == 'pending' && ((string)$Transaction->CheckoutDetails->CheckoutID != '')) {
                                $skeepOrder = true;
								break;
							} elseif ((!empty($invoiceId) && $order->getQuicksalesOrderId() == $invoiceId)  || !$order->canCancel()) {
                                $skeepOrder = true;
								break;
                            }
                        }
                    }
                }

                if ($skeepOrder /*&& $qhash != 1314*/) {
                    continue;
                }

                try {
                    $customer = $this->prepareCustomer($Invoice->BuyerDetails);
                    $customer->setData('qhash', $qhash);

                    $order = $this->placeOrder($customer, array($Transaction), $ordersCount);
                    if ((string)$Invoice->InvoiceID != '') {
                        $order->setQuicksalesOrderId((string)$Invoice->InvoiceID);
                        $order->save();
                    }

                    if ((string)$Transaction->CheckoutDetails->PaypalTransaction->TransactionID != '' ) {
                        if ($order->canInvoice()) {
                            /**
                             * Create invoice
                             * The invoice will be in 'Pending' state
                             */
                            $invoiceId = Mage::getModel('sales/order_invoice_api')
                                ->create($order->getIncrementId(), array());

                            $invoice = Mage::getModel('sales/order_invoice')
                                ->loadByIncrementId($invoiceId);

                            $order->load($order->getId());

                            /**
                             * Pay invoice
                             * i.e. the invoice state is now changed to 'Paid'
                             */
                            $invoice->capture()->save();

                            Mage::getModel('quicksales/observer')->paidOrder($order);
                            $order->setState('qpaid', true);
                            $order->save();
                        }
                    }

                    $message .= $this->_listing_log->getMessage();

                    $message .= "<br />\n New order: #<a href='" . Mage::getModel('core/url')->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId())) . "'>" . $order->getIncrementId() . "</a><br />\n";

                    $pendingOrders[$qhash] = $order->getId();
                    $importedOrders[$order->getId()] = $order->getIncrementId();

                } catch (Exception $e) {
                    $message .= "<br />\n" . $e->getMessage();
                    $this->_listing_log->setResult(0);
                }
            }

            $this->_listing_log->setMessage($message);
        }

        $this->_listing_log->save();

        $orders = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('entity_id', array('nin' => $pendingOrders))
            ->addFieldToFilter('status', array('pending', 'qnot_checked_out'))
            ->addFieldToFilter('qhash', array('neq' => ''));

        foreach ($orders as $order) {
            /* @var $order Mage_Sales_Model_Order */

            if ($order->canCancel()) {
                $order->getPayment()->cancel();
                $order_str = '';
                if ($pendingOrders[$order->getQhash()]) {
                    $order_str = "New order is #" . $importedOrders[$pendingOrders[$order->getQhash()]];
                }
                $order->registerCancellation('Order canceled due to customer checkout. ' . $order_str);


                Mage::dispatchEvent('order_cancel_after', array('order' => $order));
            }
            $order->save();
        }

        //echo 'Done';
        file_put_contents('GetOrders_Responce.xml', $xmlResponce->getXmlString());

    }

    public function prepareCustomer($xmlInfo)
    {

        $customerImportConfig = Mage::getStoreConfig('quicksales/order_customer/customer_import');

        $customerObject = null;

        switch ($customerImportConfig) {
            case 'guest':
                $customerObject = Mage::getModel('customer/customer');
                $username = explode(' ', $xmlInfo->Name);
                $customerObject->setFirstname($username[0]);
                $customerObject->setLastname($username[1]);
                $customerObject->setEmail((string)$xmlInfo->Email);
                $customerObject->setMode('guest');
                break;
            case 'import':
                $customerObject = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($xmlInfo->Email);
                $customerId = $customerObject->getId();

//                if (!$customerObject->getId()) {
                    $customerObject->setEmail($xmlInfo->Email);
                    $username = explode(' ', $xmlInfo->Name);
                    $customerObject->setFirstname($username[0]);
                    $customerObject->setLastname($username[1]);
                    $customerObject->setConfirmation(Mage::getStoreConfig('quicksales/order_customer/customer_notify'));
                    $customerObject->setPassword($customerObject->generatePassword());

                    $customerObject->save();

                    if (!$customerId && Mage::getStoreConfig('quicksales/order_customer/customer_notify') == 1) {
                        $customerObject->sendNewAccountEmail('registered');
                    }
//                }

                $username = explode(' ', $xmlInfo->Name);

                $_custom_address = array(
                    'firstname' => $username[0],
                    'lastname' => $username[1],
                    'street' => array(
                        '0' => (string)$xmlInfo->Address,
                        '1' => '',
                    ),

                    'city' => (string)$xmlInfo->Suburb,
                    'region_id' => '',
                    'region' => (string)$xmlInfo->State,
                    'postcode' => (string)$xmlInfo->Postcode,
                    'country_id' => 'AU',
                );

                //$customAddress = Mage::getModel('customer/address');
                $customAddress = $customerObject->getDefaultShippingAddress();
                if (!$customAddress) {
                    $customAddress = Mage::getModel('customer/address')
                        ->setCustomer($customerObject)
                        ->setIsDefaultBilling('1')
                        ->setIsDefaultShipping('1');
                }
                $customAddress->addData($_custom_address);

                $customAddress->save();

                if (!$customerObject->getFirstname() || !$customerObject->getLastname()) {
                    $username = explode(' ', $xmlInfo->Name);
                    $customerObject->setFirstname($username[0]);
                    $customerObject->setLastname($username[1]);
                    $customerObject->save();
                }

                break;
            case 'predefined':
                $customerObject = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail(Mage::getStoreConfig('quicksales/order_customer/customer_email'));

                if (!$customerObject->getId()) {
                    $customerObject->setEmail(Mage::getStoreConfig('quicksales/order_customer/customer_email'));
                    $username = explode(' ', $xmlInfo->Name);
                    $customerObject->setFirstname($username[0]);
                    $customerObject->setLastname($username[1]);
                    $customerObject->setConfirmation(Mage::getStoreConfig('quicksales/order_customer/customer_notify'));
                    $customerObject->save();
                }

                break;
        }

        $username = explode(' ', $xmlInfo->Name);

        $_custom_address = array(
            'firstname' => $username[0],
            'lastname' => $username[1],
            'street' => array(
                '0' => (string)$xmlInfo->Address,
                '1' => '',
            ),

            'city' => (string)$xmlInfo->Suburb,
            'region_id' => '',
            'region' => (string)$xmlInfo->State,
            'postcode' => (string)$xmlInfo->Postcode,
            'country_id' => 'AU',
        );

        $customAddress = $customerObject->getDefaultShippingAddress();
        if (!$customAddress) {
            $customAddress = Mage::getModel('customer/address')
                ->setCustomer($customerObject)
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1');
        }
        $customAddress->addData($_custom_address);
        $customerObject->setAddress($customAddress);

        return $customerObject;
    }

    public function placeOrder($customerObj, $items, $ordersCount = 1)
    {
        if (!Mage::registry('isSecureArea')) {
            Mage::register('isSecureArea', true);
        }
        $customerMessage = '';

        $quoteObj = Mage::getModel('sales/quote')->assignCustomer($customerObj);

        $quoteObj->getBillingAddress()->addData($customerObj->getAddress()->getData());
        $quoteObj->getShippingAddress()->addData($customerObj->getAddress()->getData());

        $quoteObj->reserveOrderId();

        $checkoutIds = array();

        $total = $shippingCost = $discount = 0;
        $shippingMethod = '';
        $checkoutId = '';
        $qpaymentInfo = '';
        foreach ($items as $item) {

            /**
             * @var $productListing Ewave_Quicksales_Model_Listing_Product
            */
            $productListing = Mage::getModel('quicksales/listing_product')->load((string)$item->ListingID, 'quicksale_listing_id');

            /**
             * @var $listing Ewave_Quicksales_Model_Listing
            */
            $listing = Mage::getModel('quicksales/listing')->load($productListing->getListingId());

            $shippingMethod = Mage::getModel('quicksales/source_shipping')->getLabelById((string)$item->Postage->Type);

            $productObj = Mage::getModel('catalog/product')->load($productListing->getProductId());

            $productObj->setQty((string)$item->QuantitySold);
            $productObj->setPrice((string)$item->UnitPrice);

            //$productObj->setDiscountAmount((string)$item->UnitPrice);

            $productObj->setCost((string)$item->UnitPrice);
            $productObj->setSpecialPrice((string)$item->UnitPrice);
            $productObj->setStatus(1);
            $productObj->setName((string)$item->Title);

            $productObj->setQitemId((string)$item->ItemID);


            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($productObj);

            $quoteItem->setData('qty', (int)$item->QuantitySold);

            $customerName = explode(' ', (string)$item->CheckoutDetails->ShippingDetails->Name);

            if ((string)$item->CheckoutDetails->ShippingDetails->Postcode != '') {
                $_custom_address = array(
                    'firstname' => $customerName[0],
                    'lastname' => $customerName[1],
                    'street' => array(
                        0 => (string)$item->CheckoutDetails->ShippingDetails->Address
                    ),
                    'city' => (string)$item->CheckoutDetails->ShippingDetails->Suburb,
                    'region' => (string)$item->CheckoutDetails->ShippingDetails->State,
                    'postcode' => (string)$item->CheckoutDetails->ShippingDetails->Postcode,
                );
            } else {

                $_custom_address = $customerObj->getDefaultShippingAddress();

            }

            $quoteObj->getShippingAddress()->addData($_custom_address );

            $checkoutId = (string)$item->CheckoutDetails->CheckoutID;

            //if (!$checkoutIds[(string)$item->CheckoutDetails->CheckoutID]) {
                $customerMessage .= "\n" . (string)$item->Title . ': ' . (string)$item->CheckoutDetails->BuyerMessage;

                //$shippingCost = (float)$item->Postage->SingleItemPostageAmount;
                $shippingCost = (float)$item->CheckoutDetails->PostageAndHandling;


                $chargeOrDiscount = (float)$item->CheckoutDetails->SellerDiscountORCharges;

                //if ($chargeOrDiscount < 0) {
                $discount += $chargeOrDiscount;
                //}


                $discount = ($discount/$ordersCount);
                $shippingCost = ($shippingCost/$ordersCount);

                if ((string)$item->CheckoutDetails->CheckoutID == '' || !$shippingCost) {
                    $shippingCost = (float)$item->Postage->SingleItemPostageAmount;
                }

                $qpaymentInfo = (string)$item->CheckoutDetails->BuyerPaymentOption;
              //  $checkoutIds[(string)$item->CheckoutDetails->CheckoutID] = 1;
                $total = (float)$item->UnitPrice * (float)$item->QuantitySold + $shippingCost + $discount;
            //}

            $quoteItem->setDiscountAmount($discount/$productObj->getQty());
            $quoteItem->setBaseDiscountAmount($discount/$productObj->getQty());

            $quoteObj->addItem($quoteItem);

        }

//---start-update-address-------------------
        $customAddress = $customerObj->getDefaultShippingAddress();
        if (!$customAddress) {
            $customAddress = Mage::getModel('customer/address')
                ->setCustomer($customerObj)
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1');
        }
        $customAddress->addData($_custom_address);
        if ($customerObj->getId()) {
            $customAddress->save();
        }

//---end-update-address-------------------

        $quoteObj->collectTotals();

        $quoteObj->save();

        $quotePaymentObj = $quoteObj->getPayment();
        //methods: authorizenet, paypal_express, googlecheckout, purchaseorder
        $quotePaymentObj->setMethod('quicksales');
        //$quotePaymentObj->canRefund();
        $quoteObj->setPayment($quotePaymentObj);

        $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
        /* @var $orderObj Mage_Sales_Model_Order */
        $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
        $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);

        $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
        $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
        $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));

        foreach ($quoteObj->getAllItems() as $item) {
            /*
            @var $item Mage_Sales_Model_Quote_Item
            */
            $orderItem = $convertQuoteObj->itemToOrderItem($item);
            $orderItem->setQitemId($orderItem->getProduct()->getQitemId());
            if ($item->getParentItem()) {
                $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $orderItem->setDiscountAmount(-$discount);
            $orderItem->setBaseDiscountAmount(-$discount);
            $orderObj->addItem($orderItem);
        }


        $orderObj->setQpaymentInfo($qpaymentInfo);

        $orderObj->setCanShipPartiallyItem(false);
        $orderObj->setQhash($customerObj->getQhash());

        $orderObj->setGrandTotal($total);

        $orderObj->setShippingMethod('flatrate_flatrate');
        $orderObj->setShippingDescription($shippingMethod);


        $orderObj->setBaseShippingAmount($shippingCost);
        $orderObj->setShippingAmount($shippingCost);

        $orderObj->setBaseDiscountAmount($discount);
        $orderObj->setDiscountAmount($discount);


        $orderObj->setCustomerEmail($customerObj->getEmail());

        /*
        $orderObj->setShippingInclTax($shippingCost);
        $orderObj->setBaseShippingInclTax($shippingCost);
        */

        $totalDue = $orderObj->getTotalDue();

        $orderObj->setQsource(1);
        $orderObj->place(); //calls _placePayment

        Mage::app()->getStore(null)->setConfig('cataloginventory/options/can_subtract', Mage::getStoreConfig('quicksales/stock/update_magento_qty_quicksale_sold'));
        Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order' => $orderObj, 'quote' => $quoteObj));

        if (!$checkoutId) {
            $orderObj->setState('qnot_checked_out', true);
        }
        $orderObj->save();
        //$order = Mage::getModel('sales/order')->load($orderObj->getId());

        //$orderId = $orderObj->getId();
        //      echo "<p>orderId: $orderId </p>";

        return $orderObj;

    }
} 