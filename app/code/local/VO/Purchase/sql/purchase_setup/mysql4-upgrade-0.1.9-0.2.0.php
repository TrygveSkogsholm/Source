<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_prices')} 
ADD COLUMN `change_text` VARCHAR(45) NULL  AFTER `is_predictive` , 
CHANGE COLUMN `new_retail_cost` `new_retail_cost` FLOAT NULL  AFTER `name` , 
CHANGE COLUMN `old_retail_cost` `old_retail_cost` FLOAT NULL  AFTER `new_retail_cost` , 
CHANGE COLUMN `new_oem_cost` `new_oem_cost` FLOAT NULL  AFTER `new_wholesale_cost` , 
CHANGE COLUMN `old_oem_cost` `old_oem_cost` FLOAT NULL  AFTER `new_oem_cost` , 
CHANGE COLUMN `old_landed_cost` `old_landed_cost` FLOAT NULL  , 
CHANGE COLUMN `new_landed_cost` `new_landed_cost` FLOAT NULL  , 
CHANGE COLUMN `old_distributor_cost` `old_distributor_cost` FLOAT NULL  , 
CHANGE COLUMN `new_distributor_cost` `new_distributor_cost` FLOAT NULL  , 
CHANGE COLUMN `old_wholesale_cost` `old_wholesale_cost` FLOAT NULL  , 
CHANGE COLUMN `new_wholesale_cost` `new_wholesale_cost` FLOAT NULL  , 
CHANGE COLUMN `change_id` `plan_id` INT(11) NULL DEFAULT NULL  ;
    ");

$installer->endSetup();