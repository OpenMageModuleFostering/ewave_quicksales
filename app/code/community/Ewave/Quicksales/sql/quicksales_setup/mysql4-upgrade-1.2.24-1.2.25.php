<?php
$installer = $this;
$installer->startSetup();

$installer->run("
        ALTER TABLE
        {$this->getTable('listing_log')}
          ADD `order_id` int(10) unsigned NOT NULL
");
$installer->run("
        ALTER TABLE
        {$this->getTable('listing_log')}
          ADD `result` int(1) unsigned NOT NULL default 1
");

$installer->endSetup();