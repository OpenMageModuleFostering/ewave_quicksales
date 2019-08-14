<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->removeAttribute('quicksales_listing', 'default_payment_conf');
$setup->removeAttribute('quicksales_listing', 'payment_information');

$setup->removeAttribute('quicksales_listing', 'payments');
$setup->removeAttribute('quicksales_listing', 'payments_instruction');

$setup->addAttribute('quicksales_listing', 'default_payment_conf', array(
    'group'             => 'Step 2',
    'type'              => 'int',
    'label'             => 'Use default Payment information',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
));


// recurring payment profile
$setup->addAttribute('quicksales_listing', 'payment_information', array(
    'group'             => 'Step 2',
    'type'              => 'text',
    'backend'           => 'quicksales/listing_attribute_backend_payment',
    'label'             => 'Payment information',
    'input'             => 'text', // doesn't matter
    'required'          => false,
));

$installer->endSetup();