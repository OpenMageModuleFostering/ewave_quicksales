<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 15:09
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Block_Adminhtml_Listing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('listingGrid');
        $this->setDefaultSort('listing_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('quicksales/listing')->getCollection()->addAttributeToSelect('*');

        $this->setCollection($collection);
        //Mage::getModel('quicksales/listing')->load(1);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'listing_id',
            array(
                'header' => Mage::helper('quicksales')->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'entity_id',
            )
        );

        $this->addColumn(
            'created_date',
            array(
                'header' => Mage::helper('quicksales')->__('Created date'),
                'align' => 'right',
                'width' => '160px',
                'index' => 'created_at',
                'type' => 'datetime'
            )
        );

        $this->addColumn(
            'name',
            array(
                'header' => Mage::helper('quicksales')->__('Name'),
                'index' => 'name',
                'align' => 'right',
            )
        );

        $this->addColumn(
            'products_count',
            array(
                'header' => Mage::helper('quicksales')->__('Number of products'),
                //'format' => '$products_count',
                'getter' => 'getProductsCount',
                'align' => 'right',
                'width' => '160px',
            )
        );
        /* @var $quicksaleCategories Ewave_Quicksales_Model_Api_Getcategories */
        $quicksaleCategories = Mage::getSingleton('quicksales/api_getcategories')->setQuiet(true);



        $this->addColumn(
            'category',
            array(
                'header' => Mage::helper('quicksales')->__('Category'),
                'index' => 'category',
                'type' => 'options',
                'sortable' => false,
                'options' => $quicksaleCategories->getCategoryCache(),
            )
        );

        $this->addColumn(
            'update_date',
            array(
                'header' => Mage::helper('quicksales')->__('Update date'),
                'align' => 'right',
                'width' => '160px',
                'index' => 'updated_at',
                'type' => 'datetime'
            )
        );


        $this->addColumn('status',
            array(
                'header' => Mage::helper('catalog')->__('Active'),
                'width' => '70px',
                'index' => 'status',
                'type' => 'options',
                'options' => Mage::getSingleton('eav/entity_attribute_source_boolean')->getOptionArray(),
            ));

        $this->addColumn(
            'action',
            array(
                'header' => Mage::helper('catalog')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Log'),
                        'url' => array(
                            'base' => '*/*/log',
                            'params' => array('store' => $this->getRequest()->getParam('store'))
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
            ));


        return $this;
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/edit', array('id' => $item->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('listingMass');


        $this->getMassactionBlock()->addItem('stop', array(
            'label' => Mage::helper('quicksales')->__('Stop'),
            'url' => $this->getUrl('*/*/massStop', array('_current' => true))
        ));

        $this->getMassactionBlock()->addItem('relist', array(
            'label' => Mage::helper('quicksales')->__('Relist'),
            'url' => $this->getUrl('*/*/massRelist', array('_current' => true))
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('quicksales')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('quicksales')->__('Are you sure?')
        ));

        return $this;
    }

}