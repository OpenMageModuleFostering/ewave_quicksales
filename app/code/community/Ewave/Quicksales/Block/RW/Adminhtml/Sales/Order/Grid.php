<?php

class Ewave_Quicksales_Block_RW_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $collection = $this->getCollection();
        $collection->addAttributeToSelect('qsource');
        $this->setCollection($collection);

        return $this;
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'qsource',
            array(
                'header' => Mage::helper('quicksales')->__('Source'),
                'align' => 'right',
                'width' => '100px',
                'index' => 'qsource',
                'type' => 'options',
                'options' => array(
                    '0' => 'magento',
                    '1' => 'quicksales'
                )
            )
        );

        return parent::_prepareColumns();
    }

}