<?php

$installer = $this;
$installer->startSetup();


$installer->run("

        ALTER TABLE listing_product ADD UNIQUE (listing_id, product_id);
        ALTER TABLE listing_attribute ADD UNIQUE (listing_id, qattribute_id, mattribute_id);
        ALTER TABLE listing_attribute_value ADD UNIQUE (attribute_map_id, qattribute_value_id, mattribute_value_id);
        ALTER TABLE listing_product_log ADD listing_log_id int(10) unsigned NOT NULL;
");

$installer->endSetup();