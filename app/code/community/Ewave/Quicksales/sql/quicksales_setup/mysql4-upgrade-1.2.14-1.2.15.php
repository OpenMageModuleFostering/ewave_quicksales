<?php

$installer = $this;
$installer->startSetup();

$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('listing_log')};
        CREATE TABLE
        {$this->getTable('listing_log')} (
        `id` int(10) unsigned NOT NULL auto_increment,
        `listing_id` int(10) unsigned NOT NULL,
        `message` TEXT,
        `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY USING BTREE (`id`)
        );
");

$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('listing_product_log')};
        CREATE TABLE
        {$this->getTable('listing_product_log')} (
        `id` int(10) unsigned NOT NULL auto_increment,
        `association_id` int(10) unsigned NOT NULL,
        `message` TEXT,
        `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY USING BTREE (`id`)
        );
");

$installer->endSetup();