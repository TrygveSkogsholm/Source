<?php
class VO_Purchase_Block_Suppliers_Edit_Tab_Addproducts extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('SupaddProductGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(false);
	}

	protected function _prepareCollection()
	{

		if ( Mage::getSingleton('adminhtml/session')->getSupplierData() )
		{
			$id = Mage::getSingleton('adminhtml/session')->getSupplierData();
			Mage::getSingleton('adminhtml/session')->setSupplierData(null);
		} elseif ( Mage::registry('supplier_data') ) {
			$id = Mage::registry('supplier_data')->getData('id');
		}

		$currentSupplierProducts = array();
		foreach (Mage::getModel('purchase/supplier_product')->getCollection()->addFieldToFilter('supplier_id',$id) as $supProduct)
		{
			$currentSupplierProducts[] = $supProduct->getproduct_id();
		}



		$collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect(array('type_id','sku','manufacturer','name'))
		->addAttributeToSelect('manufacturer')
		->addFieldToFilter('type_id','simple');
		if (!empty($currentSupplierProducts))
		{
		$collection->addFieldToFilter('entity_id', array('nin' => $currentSupplierProducts));
		}
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$manufacturer_items = Mage::getModel('eav/entity_attribute_option')->getCollection()->setStoreFilter()->join('attribute','attribute.attribute_id=main_table.attribute_id', 'attribute_code');
        foreach ($manufacturer_items as $manufacturer_item) :
            if ($manufacturer_item->getAttributeCode() == 'manufacturer')
                $manufacturer_options[$manufacturer_item->getOptionId()] = $manufacturer_item->getValue();
        endforeach;


		$this->addColumn('add', array(
          'header'    => Mage::helper('purchase')->__('Add'),
		'renderer' => 'VO_Purchase_Block_Suppliers_Edit_Tab_Renderer_Select',
        'width'     => '50px',
		'filter'	=> false,
		'sortable'  => false,
			'align'     => 'center',
          'index'     => 'add'

          ));

          $this->addColumn('entity_id', array(
          'header'    => Mage::helper('purchase')->__('ID'),
		'getter'    => 'getId',
          'width'     => '50px',
          'index'     => 'entity_id'
          ));

          $this->addColumn('sku', array(
          'header'    => Mage::helper('purchase')->__('SKU'),
		  'type'	=> 'text',
          'index'     => 'sku'
          ));

          $this->addColumn('name', array(
          'header'    => Mage::helper('purchase')->__('Name'),
		  'type'	=> 'text',
          'index'     => 'name',
          'width'     => '500'
          ));


                  $this->addColumn('manufacturer',
            array(
                'header'=> Mage::helper('catalog')->__('Manufacturer'),
                'width' => '100px',
                'type'  => 'options',
                'index' => 'manufacturer',
                'options' => $manufacturer_options
        ));


          $this->addColumn('model', array(
            'header'    => Mage::helper('purchase')->__('Model #'),
            'name'      => 'model',
            'type'      => 'input',
            'index'     => 'model',
		 	'width'     => '150',
			'renderer' => 'VO_Purchase_Block_Suppliers_Edit_Tab_Renderer_Model',
            'editable'  => true,
			'filter'	=> false,
          'sortable'  => false,
            'edit_only' => false
          ));

          $this->addColumn('first_cost', array(
            'filter'    => false,
            'sortable'  => false,
            'header'    => Mage::helper('purchase')->__('First Cost'),
            'name'    	=> 'first_cost',
            'align'     => 'right',
            'type'      => 'input',
            'validate_class' => 'validate-number',
            'index'     => 'first_cost',
        'renderer' => 'VO_Purchase_Block_Suppliers_Edit_Tab_Renderer_Firstcost',
          ));

          //$this->addExportType('*/*/exportCsv', Mage::helper('purchase')->__('CSV'));
          //$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

          return parent::_prepareColumns();
	}

}
