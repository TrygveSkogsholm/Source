<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_supplier_products')}
ADD COLUMN `case_qty` INT UNSIGNED NULL  AFTER `model` ,
ADD COLUMN `rate_known` TINYINT(1) NULL DEFAULT 0  AFTER `case_qty` ,
ADD COLUMN `hts_known` TINYINT(1) NULL DEFAULT 0  AFTER `rate_known` ;
    ");

$installer->endSetup();