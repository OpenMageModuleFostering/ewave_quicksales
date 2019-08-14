<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->updateEntityType('quicksales_listing', 'additional_attribute_table', '');

$setup->updateAttribute('quicksales_listing',
                        'category',
                        array(
                            'frontend_input_renderer' => '',
                        )
);

$installer->endSetup();