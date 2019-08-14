<?php

$installer = $this;
$installer->startSetup();


$installer->run("
        Alter Table listing_attribute_value drop key qattribute_value_id;
        Alter Table listing_attribute_value drop key attribute_map_id_2;
        ALTER TABLE listing_attribute_value change qattribute_value_id qattribute_value_id text not null;
        ALTER TABLE listing_attribute_value ADD UNIQUE (attribute_map_id, qattribute_value_id(10), mattribute_value_id(10));
        ALTER TABLE listing_attribute_value ADD KEY `qattribute_value_id` (qattribute_value_id(10));
");

$installer->endSetup();