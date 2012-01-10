<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_prices')} 
ADD COLUMN `name` VARCHAR(45) NULL  AFTER `sku` , 
ADD COLUMN `po_id` INT NULL  AFTER `effective` , 
ADD COLUMN `ship_id` INT NULL  AFTER `po_id` , 
ADD COLUMN `change_id` INT NULL  AFTER `ship_id` , 
CHANGE COLUMN `product_id` `product_id` INT(11) NULL  ;
    ");

$installer->endSetup();