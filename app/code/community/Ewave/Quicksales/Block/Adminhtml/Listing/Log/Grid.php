<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 15:09
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Block_Adminhtml_Listing_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('listingLogGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

        if ($this->getRequest()->getParam('id')) {
            $this->setDefaultFilter(array('listing_id' => $this->getRequest()->getParam('id')));
        }
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('quicksales/listing_log')->getCollection();

        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('quicksales_listing','name');

        $collection->getSelect()->join( array('name_table' => $attribute->getBackendTable()),
                               'listing_id=name_table.entity_id AND name_table.attribute_id = "'.$attribute->getId().'"',
                               'value');

        if ($this->getRequest()->getParam('id')) {
            $collection->addFieldToFilter('listing_id', $this->getRequest()->getParam('id'));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'transaction_id',
            array(
                 'header' => Mage::helper('quicksales')->__('Transaction internal ID'),
                 'align' => 'right',
                 'width' => '100px',
                 'index' => 'id',
            )
        );

        $this->addColumn(
            'listing',
            array(
                 'header' => Mage::helper('quicksales')->__('Listing name'),
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
                 'format' => '$message',
                 'align' => 'right'
            )
        );

        $this->addColumn(
            'details',
            array(
                 'header' => Mage::helper('catalog')->__('Details'),
                 'align' => 'right',
                 'type' => 'action',
                 'getter' => 'getId',
                 'actions' => array(
                     array(
                         'caption' => Mage::helper('catalog')->__('Show detailed log'),
                         'url' => array(
                             'base' => '*/*/productlog',
                         ),
                         'field' => 'id'
                     )
                 ),
                 'filter' => false,
                 'sortable' => false,
                 'index' => 'id',
            )
        );

        return $this;
    }

}