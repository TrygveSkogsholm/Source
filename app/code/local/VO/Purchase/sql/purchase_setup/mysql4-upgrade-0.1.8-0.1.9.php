<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_prices')} 
ADD COLUMN   `old_oem_cost` FLOAT NOT NULL ,
ADD COLUMN   `new_oem_cost` FLOAT NOT NULL ;

    ");

$installer->endSetup();