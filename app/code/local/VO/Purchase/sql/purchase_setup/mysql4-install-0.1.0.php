<?php

$installer = $this;

$installer->startSetup();

$installer->run("

 DROP TABLE IF EXISTS {$this->getTable('purchase_orders')};
CREATE TABLE {$this->getTable('purchase_orders')} (
	`order_id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) NOT NULL DEFAULT '1',
	`is_paid` TINYINT(1) NOT NULL DEFAULT '0',
	`payment_method` VARCHAR(45) NULL DEFAULT NULL,
	`date_paid` DATETIME NULL DEFAULT NULL,
	`date_created` DATETIME NULL DEFAULT NULL,
	`date_sent` DATETIME NULL DEFAULT NULL,
	`supplier_id` INT(11) NOT NULL,
	`ship_from_name` TINYTEXT NULL,
	`ship_from_contact` TINYTEXT NULL,
	`ship_from_country` TINYTEXT NULL,
	`ship_from_state` TINYTEXT NULL,
	`ship_from_zip` TINYTEXT NULL,
	`ship_from_city` TINYTEXT NULL,
	`ship_from_address1` MEDIUMTEXT NULL,
	`ship_from_address2` MEDIUMTEXT NULL,
	`ship_to_name` MEDIUMTEXT NULL,
	`ship_to_contact` MEDIUMTEXT NULL,
	`ship_to_country` TINYTEXT NULL,
	`ship_to_state` TINYTEXT NULL,
	`ship_to_zip` TINYTEXT NULL,
	`ship_to_city` TINYTEXT NULL,
	`ship_to_address1` MEDIUMTEXT NULL,
	`ship_to_address2` MEDIUMTEXT NULL,
	`comments` LONGTEXT NULL,
	PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 DROP TABLE IF EXISTS {$this->getTable('purchase_order_products')};
CREATE TABLE {$this->getTable('purchase_order_products')} (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(11) UNSIGNED NOT NULL,
	`po_id` INT(11) UNSIGNED NOT NULL,
	`qty` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`is_shipped` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`is_received` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`first_cost` FLOAT NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 DROP TABLE IF EXISTS {$this->getTable('purchase_shipments')};
CREATE TABLE {$this->getTable('purchase_shipments')} (
	`id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` SMALLINT(11) NULL DEFAULT '1',
	`freight_cost` DECIMAL(12,4) NULL DEFAULT NULL,
	`edoa` DATE NULL DEFAULT NULL,
	`date_created` DATETIME NULL DEFAULT NULL,
	`date_shipped` DATE NULL DEFAULT NULL,
	`date_received` DATE NULL DEFAULT NULL,
	`supplier_id` INT(11) NOT NULL,
	`carrier` VARCHAR(45) NULL DEFAULT NULL,
	`ship_method` VARCHAR(45) NULL DEFAULT NULL,
	`comments` LONGTEXT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('purchase_shipment_products')};
CREATE TABLE {$this->getTable('purchase_shipment_products')} (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`shipment_id` INT(10) UNSIGNED NOT NULL,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`order_product_id` INT(10) UNSIGNED NOT NULL,
	`landed_cost` FLOAT UNSIGNED NULL DEFAULT NULL,
	`qty` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('purchase_product_additional')};
CREATE TABLE {$this->getTable('purchase_product_additional')} (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(11) UNSIGNED NOT NULL,
	`average_landed_cost` DECIMAL(12,4) NULL,
	`active_shipment` TEXT NULL,
	`manufacturer` VARCHAR(10) NULL,
	INDEX `PRIMARY KEY` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 DROP TABLE IF EXISTS {$this->getTable('purchase_supplier_products')};
CREATE TABLE {$this->getTable('purchase_supplier_products')} (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`supplier_id` INT(11) UNSIGNED NOT NULL,
	`product_id` INT(11) UNSIGNED NOT NULL,
	`first_cost` FLOAT(12,4) NOT NULL,
	`model` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 DROP TABLE IF EXISTS {$this->getTable('purchase_supplier')};
CREATE TABLE {$this->getTable('purchase_supplier')} (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`company_name` TEXT NOT NULL,
	`buffer_time` DECIMAL(50,4) NULL DEFAULT NULL,
	`lead_time` DECIMAL(50,4) NULL DEFAULT NULL,
	`shipping_delay` DECIMAL(50,4) NULL DEFAULT NULL,
	`default_projection_time` DECIMAL(50,4) NULL DEFAULT NULL,
	`default_carrier` VARCHAR(50) NULL DEFAULT NULL,
	`default_method` VARCHAR(50) NULL DEFAULT NULL,
	`address_additional` TEXT NULL,
	`address_state` TINYTEXT NULL,
	`address_city` TINYTEXT NULL,
	`address_country` MEDIUMTEXT NULL,
	`address_street1` TEXT NULL,
	`address_street2` TEXT NULL,
	`address_zip` MEDIUMTEXT NULL,
	`phone` TINYTEXT NULL,
	`email` MEDIUMTEXT NULL,
	`fax` TINYTEXT NULL,
	`contact_name` MEDIUMTEXT NULL,
	`is_manufacturer` TINYINT(1) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 DROP TABLE IF EXISTS {$this->getTable('hts')};
CREATE TABLE {$this->getTable('hts')} (
  `code` VARCHAR(45) NOT NULL,
  `rate` float(11) NOT NULL default 0,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 DROP TABLE IF EXISTS {$this->getTable('purchase_stock_movements')};
CREATE TABLE {$this->getTable('purchase_stock_movements')} (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(11) NOT NULL,
	`date` DATETIME NULL DEFAULT NULL,
	`type` VARCHAR(45) NOT NULL,
	`magnitude` INT(11) NOT NULL DEFAULT '0',
	`order_num` INT(11) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 DROP TABLE IF EXISTS {$this->getTable('purchase_supply_needs')};
CREATE TABLE {$this->getTable('purchase_supply_needs')} (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(11) UNSIGNED NOT NULL,
	`supplier_id` INT(11) UNSIGNED NOT NULL,
	`order_by_date` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();