<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 11:34
 * To change this template use File | Settings | File Templates.
 */
 
$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->removeAttribute('quicksales_listing', 'hot_deals');

$setup->addAttribute('quicksales_listing', 'name', array(
    'group' => 'Step 1',
    'label' => 'Name',
    'required' => false,
    'position' => 1,
    'sort_order' => 40,
    'visible' => 1,
    )
)
;

$installer->endSetup();