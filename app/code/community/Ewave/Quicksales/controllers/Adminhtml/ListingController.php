<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Adminhtml_ListingController extends Mage_Adminhtml_Controller_Action
{

    protected function _initListing()
    {

        $this->_title($this->__('Listing'));

        $listingId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');
        $listing = Mage::getModel('quicksales/listing');

        if ($listingId) {
            $listing->load($listingId);
        }

        if ($activeTabId = (string)$this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        Mage::register('listing', $listing);
        Mage::register('current_listing', $listing);
        return $listing;
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('quicksales/listing')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Listing'), Mage::helper('adminhtml')->__('Listing'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_initListing();
        $this->loadLayout()
            ->_setActiveMenu('quicksales/listing')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Listing'), Mage::helper('adminhtml')->__('Listing'));
        $this->renderLayout();
        $session = Mage::getSingleton('admin/session');
        $session->unsQuicksalesProductIds();
        $session->unsQuicksalesAttrubutesAssign();
        return $this;
    }

    public function editAction()
    {
        $listingId = (int)$this->getRequest()->getParam('id');
        $listing = $this->_initListing();

        $session = Mage::getSingleton('admin/session');
        $session->unsQuicksalesAttrubutesAssign();

        if ($listingId && !$listing->getId()) {
            $this->_getSession()->addError(Mage::helper('catalog')->__('This product no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($listing->getName());

        Mage::dispatchEvent('quicksales_listing_edit_action', array('listing' => $listing));

        $this->loadLayout()
            ->_setActiveMenu('quicksales/listing')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Listing'), Mage::helper('adminhtml')->__('Listing'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    public function listingsAction()
    {

        $this->_initListing();
        $this->loadLayout(false);
        $this->renderLayout();

    }

    protected function _initListingSave()
    {
        $listing = $this->_initListing();
        $listingData = $this->getRequest()->getPost('listing');

        $listing
            ->unsAttributesAssociations()
            ->unsAttributeValuesAssociations();
        $listing->addData($listingData);

        Mage::dispatchEvent(
            'quicksales_listing_prepare_save',
            array('listing' => $listing, 'request' => $this->getRequest())
        );

        return $listing;
    }

    public function saveAction()
    {

        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $isEdit = (int)($this->getRequest()->getParam('id') != null);

        $data = $this->getRequest()->getPost();

        if ($data) {

            $listing = $this->_initListingSave()->save();

            try {
                $products = Mage::getResourceModel('quicksales/listing_product_collection');
                $products->addFieldToFilter('listing_id', $listing->getId());
                $pids = array();
                foreach ($products as $product) {
                    if (!in_array($product->getProductId(), $listing->getData('assigned_products'))) {
                        $product->delete();
                    } else {
                        $pids[] = $product->getProductId();
                    }
                }

                if ($listing->getData('assigned_products')) {
                    foreach ($listing->getData('assigned_products') as $productId) {
                        if (in_array($productId, $pids)) {
                            continue;
                        }
                        $product = Mage::getModel('quicksales/listing_product'); //->load($productId . '_' . $listing->getId());
                        $product
                            ->setListingId($listing->getId())
                            ->setProductId($productId)
                            ->save();
                    }
                }

                $oldAttributes = Mage::getResourceModel('quicksales/listing_associated_attribute_collection');
                $oldAttributes->addFieldToFilter('listing_id', $listing->getId());
                $attributeIds = array();

                $attributeAssociation = $listing->getData('attributes_association');

                foreach ($oldAttributes as $oldAttribute) {
                    if ($attributeAssociation[$oldAttribute->getQattributeId()] != $oldAttribute->getMattributeId()) {
                        $oldAttribute->delete();
                    } else {
                        $attributeIds[$oldAttribute->getAttributeMapId()] = array($oldAttribute->getQattributeId(), $oldAttribute->getMattributeId());
                    }
                }

                $oldAttributesValues = Mage::getResourceModel('quicksales/listing_associated_attribute_value_collection');
                $oldAttributesValues->join('listing_attribute', 'main_table.attribute_map_id = listing_attribute.attribute_map_id', array('mattribute_id', 'qattribute_id'));
                $oldAttributesValues->addFieldToFilter('listing_attribute.listing_id', $listing->getId());

                $valuesAssociations = $listing->getData('attribute_values_association');

                $attributeValueIds = array();
                foreach ($oldAttributesValues as $valueInfo) {
                    if ($valuesAssociations[$valueInfo->getQattributeId()][$valueInfo->getQattributeValueId()] != $valueInfo->getMattributeValueId()) {
                        $valueInfo->delete();
                    } else {
                        $attributeValueIds[] = array($valueInfo->getMattributeValueId(), $valueInfo->getQattributeValueId());
                    }
                }

                $associatedValues = $listing->getData('attribute_values_association');

                foreach ($listing->getData('attributes_association') as $qAttributeId => $mAttributeId) {
                    if (!$mAttributeId) {
                        continue;
                    }

                    if (!in_array(array($qAttributeId, $mAttributeId), $attributeIds)) {
                        $attributeAssociation = Mage::getModel('quicksales/listing_associated_attribute');
                        $attributeAssociation
                            ->setListingId($listing->getId())
                            ->setQattributeId($qAttributeId)
                            ->setMattributeId($mAttributeId)
                            ->save();
                        $attributeAssociationId = $attributeAssociation->getId();
                    } else {
                        $attributeAssociationId = array_search(array($qAttributeId, $mAttributeId), $attributeIds);
                    }

                    if (!empty($associatedValues[$qAttributeId])) {
                        foreach ($associatedValues[$qAttributeId] as $qAttributeValue => $mAttributeValue) {

                            if (in_array(array($mAttributeValue, $qAttributeValue), $attributeValueIds)) {
                                continue;
                            }

                            $attributeValueAssociation = Mage::getModel('quicksales/listing_associated_attribute_value');
                            $attributeValueAssociation
                                ->setAttributeMapId($attributeAssociationId)
                                ->setQattributeValueId($qAttributeValue)
                                ->setMattributeValueId($mAttributeValue)
                                ->save();
                        }
                    }
                }

                Mage::getModel('quicksales/api_createitem')->send($listing);

            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setListingData($data);
                $redirectBack = true;
            }

        }


        if ($redirectBack) {
            $this->_redirect('*/*/edit',
                array(
                    'id' => $listing->getId(),
                    '_current' => true
                )
            );
        } else {
            $this->_redirect('*/*/',
                array('store' => $storeId)
            );
        }

    }

    /**
     * Delete listing action
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $listing = Mage::getModel('quicksales/listing')
                ->load($id);
            try {
                Mage::getModel('quicksales/listing_action')->stop($this->getRequest()->getParam('id'));
                $listing->delete();
                $this->_getSession()->addSuccess($this->__('The listing has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()
            ->setRedirect($this->getUrl('*/*/'));
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        try {
            $listingData = $this->getRequest()->getPost('listing');

            /* @var $listing Mage_Catalog_Model_Product */
            $listing = Mage::getModel('quicksales/listing');
            $listing->setData('_edit_mode', true);

            if ($listingId = $this->getRequest()->getParam('id')) {
                $listing->load($listingId);
            }

            $dateFields = array();
            $attributes = $listing->getAttributes();
            foreach ($attributes as $attrKey => $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    if (array_key_exists($attrKey, $listingData) && $listingData[$attrKey] != '') {
                        $dateFields[] = $attrKey;
                    }
                }
            }
            $listingData = $this->_filterDates($listingData, $dateFields);

            $listing->addData($listingData);
            //$listing->validate();
        }
        catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function getListing()
    {
        $listing = Mage::registry('current_listing');
        if (!$listing) {
            $listing = $this->_initListing();
        }
        return $listing;
    }

    public function attributesAction()
    {

        //Mage::register('listingCategoryId', (int)$this->getRequest()->getParam('categoryId'));

        $this->getListing()->setCategory((int)$this->getRequest()->getParam('categoryId'));
        $product_ids = explode(',', trim($this->getRequest()->getParam('productIds'), ','));
        $session = Mage::getSingleton('admin/session');
        $session->setQuicksalesProductIds($product_ids);
        $session->unsQuicksalesAttrubutesAssign();

        //Mage::getModel('quicksales/api_gettags')->getAttributes((int)$this->getRequest()->getParam('categoryId'));
        $this->loadLayout(false)->renderLayout();

        return $this;

    }

    public function attributeValuesAction()
    {

        $this->_initListing();

        $mAttributeCode = $this->getRequest()->getParam('mAttributeId');
        $elementName = $this->getRequest()->getParam('elementName');
        $qAttributeId = preg_replace('/listing\[attributes_association\]\[(.*)\]/', '$1', $elementName); //listing[attributes_association][1]

        $session = Mage::getSingleton('admin/session');

        $savedAttributes = $session->setLastAttributes();

        //Mage::helper('quicksales')->getAssociatedGridHtml($qc_id);

        $attributes = $session->getQuicksalesAttrubutesAssign();
        $post = $this->getRequest()->getPost();
        if (!is_array($attributes) || (!empty($post) && empty($mAttributeCode))) {
            $attributes[$qAttributeId] = $mAttributeCode;
        }

        $attributes = $session->setQuicksalesAttrubutesAssign($attributes);
        echo Mage::helper('quicksales')->getAssociatedGridHtml($qAttributeId, $mAttributeCode);
    }

    public function subcategoriesAction()
    {

        $parentId = (int)$this->getRequest()->getParam('parentId');

        $subcategories = Mage::getModel('quicksales/api_getcategories')->getAllOptions(false, false, $parentId);
        if (empty($subcategories)) {
            echo '{}';
        } else {
            echo Mage::helper('core')->jsonEncode($subcategories);
        }

    }

    /**
     * Get super config grid
     *
     */
    public function productsAction()
    {
        $this->_initListing();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function logAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('quicksales/listing')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Listing'), Mage::helper('adminhtml')->__('Listing'));

        $this->getLayout();
        $this->renderLayout();
    }

    public function productlogAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('quicksales/listing')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Listing'), Mage::helper('adminhtml')->__('Listing'));

        $this->getLayout();
        $this->renderLayout();
    }

    public function stopAction()
    {
        Mage::getModel('quicksales/listing_action')->stop($this->getRequest()->getParam('id'));
        $this->_redirect('*/*/edit',
            array(
                'id' => $this->getRequest()->getParam('id'),
                '_current' => true
            )
        );
    }

    public function startAction()
    {
        Mage::getModel('quicksales/listing_action')->start($this->getRequest()->getParam('id'));
        $this->_redirect('*/*/edit',
            array(
                'id' => $this->getRequest()->getParam('id'),
                '_current' => true
            )
        );
    }

    public function relistAction()
    {
        Mage::getModel('quicksales/listing_action')->relist($this->getRequest()->getParam('id'));
        $this->_redirect('*/*/edit',
            array(
                'id' => $this->getRequest()->getParam('id'),
                '_current' => true
            )
        );
    }

    public function massStopAction()
    {
        $listingIds = (array)$this->getRequest()->getParam('listingMass');

        try {

            foreach ($listingIds as $listingID) {
                Mage::getModel('quicksales/listing_action')->stop($listingID);
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($listingID))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the listing(s).'));
        }

        $this->_redirect('*/*/index');
    }

    public function massRelistAction()
    {
        $listingIds = (array)$this->getRequest()->getParam('listingMass');

        try {

            foreach ($listingIds as $listingID) {
                Mage::getModel('quicksales/listing_action')->relist($listingID);
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($listingIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the listing(s).'));
        }

        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $listingIds = (array)$this->getRequest()->getParam('listingMass');

        $counter = 0;

        try {

            foreach ($listingIds as $listingID) {
                $listing = Mage::getModel('quicksales/listing')
                    ->load($listingID);
                try {
                    Mage::getModel('quicksales/listing_action')->stop($listingID);
                    $listing->delete();
                    $counter++;
                } catch (Exception $e) {

                    $this->_getSession()
                        ->addException($e, $this->__('An error occurred while deleting the listing(s).'));
                }
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', $counter)
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while deleting the listing(s).'));
        }

        $this->_redirect('*/*/index');
    }
}
