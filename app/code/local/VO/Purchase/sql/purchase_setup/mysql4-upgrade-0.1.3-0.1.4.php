<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_stock_movements')}
	CHANGE COLUMN `stock-after` `stockafter` INT(11) NOT NULL AFTER `magnitude`
    ");

$installer->endSetup();