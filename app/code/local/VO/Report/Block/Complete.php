<?php
class VO_Report_Block_Complete extends Mage_Adminhtml_Block_Template
{
  public function __construct()
  {
    $this->_headerText = 'Complete Sales By SKU';
    $this->setTemplate('vo_report/complete.phtml');
    parent::__construct();
  }
}