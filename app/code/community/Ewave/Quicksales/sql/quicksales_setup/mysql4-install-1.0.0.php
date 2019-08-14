<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 15:27
 * To change this template use File | Settings | File Templates.
 */
 
$installer = $this;

$installer->startSetup();
/*
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('listing')};
        CREATE TABLE
        {$this->getTable('listing')} (
        `listing_id` int(10) unsigned NOT NULL auto_increment,
        `name` varchar(255) NOT NULL DEFAULT '',
        `category` varchar(255) NOT NULL DEFAULT '',
        `category_id` int(10) not null default 0,
        `extra_info_step1` text default '',
        `extra_info_step2` text default '',
        `extra_info_step3` text default '',
        `created_time` datetime DEFAULT NULL,
        `update_time` datetime DEFAULT NULL,
        `order_by` int(10) not null default 0,
        PRIMARY KEY USING BTREE (`listing_id`)
        );
");
*/
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('listing_product')};
        CREATE TABLE
        {$this->getTable('listing_product')} (
        `id` int(10) unsigned NOT NULL auto_increment,
        `listing_id` int(10) unsigned NOT NULL,
        `quicksale_listing_id` int(10) unsigned NOT NULL,
        `product_id` int(10) unsigned NOT NULL,
        KEY (`listing_id`),
        KEY (`product_id`),
        PRIMARY KEY USING BTREE (`id`)
        );
");

$installer->endSetup();