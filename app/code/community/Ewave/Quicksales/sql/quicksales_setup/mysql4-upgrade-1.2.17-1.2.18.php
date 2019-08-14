<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->removeAttribute('quicksales_listing', 'status');

$setup->addAttribute('quicksales_listing', 'status', array(
    'group'             => 'Step 1',
    'type'              => 'int',
    'label'             => 'Active',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
    'default'           => 1
));
/*
$installer->run("
        ALTER TABLE listing_product ADD status int(10) not null default 1
");
*/
$installer->endSetup();