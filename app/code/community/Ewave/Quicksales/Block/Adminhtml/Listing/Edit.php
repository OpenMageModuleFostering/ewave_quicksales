<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 14:15
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Block_Adminhtml_Listing_Edit extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quicksales/listing/edit.phtml');
        $this->setId('listing_edit');
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getListing()
    {
        return Mage::registry('current_listing');
    }

    protected function _prepareLayout()
    {

        $this->setChild('back_button',
                        $this->getLayout()
                                ->createBlock('adminhtml/widget_button')
                                ->setData(array(
                                               'label' => Mage::helper('quicksales')->__('Back'),
                                               'onclick' => 'setLocation(\'' . $this->getUrl('*/*/') . '\')',
                                               'class' => 'back'
                                          ))
        );


        $this->setChild('save_button',
                        $this->getLayout()
                                ->createBlock('adminhtml/widget_button')
                                ->setData(array(
                                               'label' => Mage::helper('quicksales')->__('Save'),
                                               'onclick' => 'listingForm.submit()',
                                               'class' => 'save'
                                          ))
        );


        $this->setChild('save_and_edit_button',
                        $this->getLayout()
                                ->createBlock('adminhtml/widget_button')
                                ->setData(array(
                                               'label' => Mage::helper('quicksales')->__('Save and Upload'),
                                               'onclick' => 'saveAndContinueEdit(\'' . $this->getSaveAndContinueUrl() . '\')',
                                               'class' => 'save'
                                          ))
        );


        $this->setChild('delete_button',
                        $this->getLayout()
                                ->createBlock('adminhtml/widget_button')
                                ->setData(array(
                                               'label' => Mage::helper('quicksales')->__('Delete'),
                                               'onclick' => 'confirmSetLocation(\'' . Mage::helper('catalog')->__('Are you sure?') . '\', \'' . $this->getDeleteUrl() . '\')',
                                               'class' => 'delete'
                                          ))
        );


        $this->setChild('previous_button',
                        $this->getLayout()
                                ->createBlock('adminhtml/widget_button')
                                ->setData(array(
                                               'label' => Mage::helper('quicksales')->__('Previous Step'),
                                               'onclick' => 'previousTab()',
                                                'id' => 'previousTab',
                                          ))
        );

        $this->setChild('next_button',
                        $this->getLayout()
                                ->createBlock('adminhtml/widget_button')
                                ->setData(array(
                                               'label' => Mage::helper('quicksales')->__('Next Step'),
                                               'onclick' => 'nextTab()',
                                                'id' => 'nextTab',
                                          ))
        );

        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveAndEditButtonHtml()
    {
        return $this->getChildHtml('save_and_edit_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getDuplicateButtonHtml()
    {
        return $this->getChildHtml('duplicate_button');
    }

    public function getPreviousButtonHtml()
    {
        return $this->getChildHtml('previous_button');
    }

        public function getNextButtonHtml()
    {
        return $this->getChildHtml('next_button');
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current' => true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => null));
    }

    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
                                              '_current' => true,
                                              'back' => 'edit',
                                              'tab' => '{{tab_id}}',
                                              'active_tab' => null
                                         ));
    }

    public function getProductId()
    {
        return $this->getListing()->getId();
    }

    public function getListingSetId()
    {
        $setId = false;
        if (!($setId = $this->getListing()->getAttributeSetId()) && $this->getRequest()) {
            $setId = $this->getRequest()->getParam('set', null);
        }
        return $setId;
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current' => true));
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', array('_current' => true));
    }

    public function getHeader()
    {
        $header = '';
        if ($this->getListing()->getId()) {
            $header = $this->htmlEscape($this->getListing()->getName());
        }
        else {
            $header = Mage::helper('quicksales')->__('New Listing');
        }
        if ($setName = $this->getAttributeSetName()) {
            $header .= ' (' . $setName . ')';
        }
        return $header;
    }

    public function getAttributeSetName()
    {
        if ($setId = $this->getListing()->getAttributeSetId()) {
            $set = Mage::getModel('eav/entity_attribute_set')
                    ->load($setId);
            return $set->getAttributeSetName();
        }
        return '';
    }

    public function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }

}
