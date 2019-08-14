<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 15:09
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
            'type',
            array(
                 'header' => Mage::helper('quicksales')->__('Type'),
                 'align' => 'right',
                 'width' => '200px',
                 'index' => 'type',
                 'type' => 'options',
                 'options' => array(
                     '1' => 'Listing',
                     '2' => 'Orders',
                     '3' => 'Orders import'
                 )
            )
        );

        $this->addColumn(
            'result',
            array(
                 'header' => Mage::helper('quicksales')->__('Result'),
                 'align' => 'right',
                 'width' => '200px',
                 'index' => 'result',
                 'type' => 'options',
                 'options' => array(
                     '0' => 'Error',
                     '1' => 'Success'

                 )
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
                             'base' => 'quicksales/adminhtml_listing/productlog',
                         ),
                         'field' => 'id'
                     )
                 ),
                 'filter' => false,
                 'sortable' => false,
                 'index' => 'id',
                 'renderer' => 'quicksales/adminhtml_log_grid_column_renderer_action'
            )
        );

        return $this;
    }

}