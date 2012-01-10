<?php
class VO_Purchase_Block_Suppliers_Edit_Tab_Products_Extended_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('extendedCosts');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/supplier_product_extended')->getCollection()
		->addFieldToFilter('sup_item_id',Mage::registry('supplier_product_data')->getid());
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('display_to_supplier', array(
            'header'=> Mage::helper('purchase')->__('Display'),
            'index' => 'display_to_supplier',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'width'     => '50px',
            'align' => 'center'
            ));

		$this->addColumn('cost', array(
          'header'    => Mage::helper('purchase')->__('Cost'),
          'index'     => 'cost'
		));
		
		$this->addColumn('name', array(
          'header'    => Mage::helper('purchase')->__('Name'),
          'index'     => 'name'
		));

            //$this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
            //$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

            return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/editExtended', array('id' => $row->getId()));
	}
}