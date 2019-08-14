<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');

$setup->addAttribute('order', 'qsource', array(
    'type'              => 'int',
    'required'          => false,
    'type' => 'int',
    'grid' => true,
    'unsigned'  => true,
    'required'  => 1,
));

$installer->run("
        UPDATE
        {$this->getTable('sales_flat_order')}
          SET
            qsource = '1'
          WHERE
            quicksales_order_id != 0
            OR
            qhash != '';


        UPDATE
        {$this->getTable('sales_flat_order_grid')}
          SET
            qsource = '1'
          WHERE
            entity_id IN (
              SELECT entity_id FROM     {$this->getTable('sales_flat_order')} WHERE
                          quicksales_order_id != 0
                          OR
                          qhash != ''
            );
");

$installer->endSetup();