<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('purchase_price_list')};
CREATE TABLE {$this->getTable('purchase_price_list')} (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `date_modified` DATETIME NULL ,
  `name` VARCHAR(45) NOT NULL ,
  `comment` MEDIUMTEXT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('purchase_price_list_price')};
CREATE TABLE {$this->getTable('purchase_price_list_price')} (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `list_id` INT NOT NULL ,
  `product_id` INT NOT NULL ,
  `sku` VARCHAR(12) NOT NULL ,
  `name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();