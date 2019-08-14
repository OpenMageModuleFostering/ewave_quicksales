<?php

/* @var $this Ewave_Quicksales_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();
$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');


$setup
        ->createEntityTables(
            $this->getTable('quicksales/listing')
        )
        ->removeEntityType('quicksales_listing')
        ->addEntityType(
            'quicksales_listing',
            array(
                'entity_model'          => 'quicksales/listing',
                'attribute_model'       => 'catalog/resource_eav_attribute',
                'table'                 => 'quicksales/listing',
                'increment_model'       => '',
                'increment_per_store'   => 0
            )
        )
        ->installEntities()
    ;
//update eav_entity_type set attribute_model='catalog/resource_eav_attribute' where entity_type_id = 33;

$installer->endSetup();