<?php
class VO_Purchase_Block_Prices_List_Prices_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
      parent::__construct();
      $this->setId('priceListProductGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::registry('price_list_data')->getProducts();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
        
		$this->addColumn('product_id', array(
          'header'    => Mage::helper('purchase')->__('Id'),
          'align'     =>'left',
          'index'     => 'product_id',
		));
		
		$this->addColumn('sku', array(
          'header'    => Mage::helper('purchase')->__('Sku'),
          'align'     =>'left',
          'index'     => 'sku',
		));
		
		$this->addColumn('name', array(
          'header'    => Mage::helper('purchase')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
		));
		
		$this->addExportType('*/*/exportListCsv', Mage::helper('purchase')->__('.csv'));
		
		return parent::_prepareColumns();
	}
	
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('remove', array(
             'label'=> Mage::helper('purchase')->__('Remove'),
             'url'  => $this->getUrl('*/*/removeListItem',array('list_id'=>Mage::registry('price_list_data')->getId())),
        	 'confirm' => 'Are you sure?'
        ));
        
        return $this;
    }
}