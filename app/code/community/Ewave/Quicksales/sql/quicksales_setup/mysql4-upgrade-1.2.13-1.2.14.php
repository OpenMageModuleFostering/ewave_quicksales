<?php

$installer = $this;
$installer->startSetup();

$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('listing_attribute')};
        CREATE TABLE
        {$this->getTable('listing_attribute')} (
        `attribute_map_id` int(10) unsigned NOT NULL auto_increment,
        `listing_id` int(10) NOT NULL,
        `qattribute_id` int(10) NOT NULL,
        `mattribute_id` int(10) NOT NULL,
        KEY (`listing_id`),
        KEY (`qattribute_id`),
        KEY (`mattribute_id`),
        PRIMARY KEY USING BTREE (`attribute_map_id`)
        );
");

$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('listing_attribute_value')};
        CREATE TABLE
        {$this->getTable('listing_attribute_value')} (
        `attribute_value_map_id` int(10) unsigned NOT NULL auto_increment,
        `attribute_map_id` int(10) unsigned NOT NULL,
        `qattribute_value_id` int(10) unsigned NOT NULL,
        `mattribute_value_id` int(10) unsigned NOT NULL,
        KEY (`attribute_map_id`),
        KEY (`qattribute_value_id`),
        KEY (`mattribute_value_id`),
        PRIMARY KEY USING BTREE (`attribute_value_map_id`)
        );
");

$installer->endSetup();