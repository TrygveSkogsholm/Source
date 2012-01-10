<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('dealers')};
CREATE TABLE {$this->getTable('dealers')} 
(
	`dealer_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`account_id` INT(13) UNSIGNED NOT NULL,
	`is_approved` INT(1) NOT NULL DEFAULT '0',
	`is_primary` INT(1) NOT NULL DEFAULT '1',
	`is_displayed` INT(1) UNSIGNED NOT NULL DEFAULT '1',
	`type` VARCHAR(32) NULL,
	`name` VARCHAR(32) NULL,
	`description` LONGTEXT NULL DEFAULT NULL,
	`country` VARCHAR(32) NULL,
	`state` VARCHAR(32) NULL,
	`zip` VARCHAR(32) NULL,
	`city` VARCHAR(32) NULL,
	`address` TEXT NULL,
	`hours` TEXT NULL,
	`phone` TEXT NULL,
	`email` TEXT NULL,
	`website` TEXT NULL,
	`longitude` FLOAT NULL,
	`latitude` FLOAT NULL,
	PRIMARY KEY (`dealer_id`),
	INDEX `account_id` (`account_id`)
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 