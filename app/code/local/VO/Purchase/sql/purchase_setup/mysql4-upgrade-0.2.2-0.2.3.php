<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('purchase_order_extended')};
CREATE TABLE {$this->getTable('purchase_order_extended')} (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `po_item_id` INT(11) UNSIGNED NOT NULL,
  `display_to_supplier` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `cost` FLOAT UNSIGNED NULL DEFAULT NULL,
  `date_modified` DATETIME NULL ,
  `name` VARCHAR(45) NOT NULL ,
  `description` MEDIUMTEXT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('purchase_default_extended')};
CREATE TABLE {$this->getTable('purchase_default_extended')} (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `sup_item_id` INT(11) UNSIGNED NOT NULL,
  `display_to_supplier` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `cost` FLOAT UNSIGNED NULL DEFAULT NULL,
  `date_modified` DATETIME NULL ,
  `name` VARCHAR(45) NOT NULL ,
  `description` MEDIUMTEXT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();