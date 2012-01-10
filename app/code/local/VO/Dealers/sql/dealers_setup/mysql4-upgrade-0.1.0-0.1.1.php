<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('dealers')} ADD COLUMN `is_found` TINYINT NOT NULL DEFAULT 1  AFTER `is_displayed` ;
    ");

$installer->endSetup();