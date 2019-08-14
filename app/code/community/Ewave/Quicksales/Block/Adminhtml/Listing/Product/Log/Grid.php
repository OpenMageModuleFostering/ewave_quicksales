<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 15:09
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Block_Adminhtml_Listing_Product_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('listingProductLogGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);


    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('quicksales/listing_product_log')->getCollection();

        $collection->getSelect()->join(array('listing_log' => 'listing_log'),
                                       'listing_log.id=listing_log_id',
                                       null);

        $collection->getSelect()->join(array('listing' => 'listing'),
                                       'listing_log.listing_id=listing.entity_id',
                                       null);
        $collection->getSelect()->join(array('listing_product' => 'listing_product'),
                                       'listing_product.id=main_table.association_id',
                                       'quicksale_listing_id');

        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('quicksales_listing', 'name');


        $collection->getSelect()->join(array('name_table' => $attribute->getBackendTable()),
                                       'listing.entity_id=name_table.entity_id AND name_table.attribute_id = "'.$attribute->getId().'"',
                                       'value');
        if ($this->getRequest()->getParam('id')) {
            $collection->addFieldToFilter('listing_log_id', $this->getRequest()->getParam('id'));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                 'header' => Mage::helper('quicksales')->__('Num'),
                 'align' => 'right',
                 'width' => '100px',
                 'index' => 'id',
            )
        );
        $this->addColumn(
            'listing_id',
            array(
                 'header' => Mage::helper('quicksales')->__('Listing id'),
                 'align' => 'right',
                 'width' => '200px',
                 'index' => 'quicksale_listing_id',
            )
        );

        $this->addColumn(
            'listing',
            array(
                 'header' => Mage::helper('quicksales')->__('Magento Listing Name'),
                 'align' => 'right',
                 'width' => '200px',
                 'index' => 'value',
            )
        );

        $this->addColumn(
            'date',
            array(
                 'header' => Mage::helper('quicksales')->__('Date'),
                 'align' => 'right',
                 'index' => 'date',
                 'width' => '200px',
                 'type' => 'datetime'
            )
        );

        $this->addColumn(
            'message',
            array(
                 'header' => Mage::helper('quicksales')->__('Message'),
                 'index' => 'message',
                 'format'   => '$message',
                 'align' => 'right',
                  'width' => '100px'
            )
        );

        return $this;
    }

}