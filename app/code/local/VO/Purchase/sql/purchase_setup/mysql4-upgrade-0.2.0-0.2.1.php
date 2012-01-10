<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_prices')} 
 CHANGE COLUMN `change_text` `change_text` TEXT NULL DEFAULT NULL
    ");

$installer->endSetup();