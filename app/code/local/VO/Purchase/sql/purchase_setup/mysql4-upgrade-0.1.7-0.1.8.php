<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('purchase_prices')} 
ADD COLUMN `is_predictive` TINYINT(1)  NULL DEFAULT FALSE;

DROP TABLE IF EXISTS {$this->getTable('purchase_price_plan')};
CREATE TABLE {$this->getTable('purchase_price_plan')} (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `date_planned` DATETIME NULL ,
  `date_created` DATETIME NULL ,
  `date_activated` DATETIME NULL ,
  `category` INT NULL ,
  `explanation` TEXT NULL ,
  `updater` VARCHAR(45) NULL ,
  
PRIMARY KEY (`id`) )
ENGINE = InnoDB;

    ");

$installer->endSetup();