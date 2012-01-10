<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('purchase_prices')};
CREATE TABLE {$this->getTable('purchase_prices')} (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `product_id` INT NOT NULL ,
  `sku` VARCHAR(45) NULL ,
  `old_landed_cost` FLOAT NOT NULL ,
  `new_landed_cost` FLOAT NOT NULL ,
  `old_distributor_cost` FLOAT NOT NULL ,
  `new_distributor_cost` FLOAT NOT NULL ,
  `old_wholesale_cost` FLOAT NOT NULL ,
  `new_wholesale_cost` FLOAT NOT NULL ,
  `old_retail_cost` FLOAT NOT NULL ,
  `new_retail_cost` FLOAT NOT NULL ,
  `average_margin` FLOAT NULL ,
  `comment` MEDIUMTEXT NULL ,
  `updater` VARCHAR(45) NULL ,
  `date` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

    ");

$installer->endSetup();