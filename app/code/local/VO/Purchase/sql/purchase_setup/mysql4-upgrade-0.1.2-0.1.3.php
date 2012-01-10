<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_stock_movements')}
	ADD COLUMN `stock-after` INT(11) NOT NULL AFTER `magnitude`
    ");

$installer->endSetup();