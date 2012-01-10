<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_prices')} 
ADD COLUMN `effective` TINYINT(1)  NULL DEFAULT FALSE  AFTER `date` ;
    ");

$installer->endSetup();