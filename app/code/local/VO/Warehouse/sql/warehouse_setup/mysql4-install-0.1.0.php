<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('warehouse_print')};
CREATE TABLE {$this->getTable('warehouse_print')} (
  `id` int(16) unsigned NOT NULL,
  `is_printed` tinyint(4) NOT NULL default '0',
  `date` datetime default NULL,
  `range_id` int(11) default NULL,
  `address_string` varchar(256) default NULL,
  `pick_date` datetime default NULL,
  `pack_date` datetime default NULL,
  `ship_date` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('warehouse_range')};
CREATE TABLE {$this->getTable('warehouse_range')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `start_id` int(11) NOT NULL,
  `start_increment` varchar(45) default NULL,
  `start_date` datetime default NULL,
  `end_id` int(255) NOT NULL,
  `end_date` datetime default NULL,
  `end_increment` varchar(45) default NULL,
  `comment` text,
  `latest` tinyint(4) NOT NULL default '1',
  `range_empty` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('warehouse_note')};
CREATE TABLE {$this->getTable('warehouse_note')} (
  `id` int(11) NOT NULL auto_increment,
  `range_id` int(11) NOT NULL,
  `print_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `comment` tinytext,
  `data` varchar(45) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup(); 