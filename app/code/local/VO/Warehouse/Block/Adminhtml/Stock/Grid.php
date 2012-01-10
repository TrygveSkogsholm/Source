<?php
class VO_Warehouse_Block_Adminhtml_Stock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('dealersGrid');
		$this->setDefaultSort('account_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('catalog/product')->getCollection()
		 ->addAttributeToSelect('sku')
		->addAttributeToSelect('binlocation')
		->addAttributeToSelect('upc')
		->addAttributeToSelect('name');

		//Parse out the bin locations and add them to the collection for the grid.
		/*foreach ($collection as $product)
		{
			$string = $product->getData('binlocation');
			$binArray = explode(',',$string);
			foreach ($binArray as $bin)
			{
				if (strpbrk($bin,'!$%') != false)
				{
					switch ($bin[0])
					{
						case '!':
							if(substr_count($bin,'!') <= 1)
							{
								$product->setData('primary_bin', ltrim($bin,'!'));
							}
							else
							{
								$product->setData('secondary_bin', ltrim($bin,'!'));
							}
							break;
							
						case '$':
							if(substr_count($bin,'$') <= 1)
							{
								$product->setData('close_overstock', ltrim($bin,'$'));
							}
							else
							{
								$product->setData('secondary_close_overstock', ltrim($bin,'$'));
							}
							break;
							
						case '%':
							if(substr_count($bin,'%') <= 1)
							{
								$product->setData('overstock', ltrim($bin,'%'));
							}
							else
							{
								$product->setData('secondary_overstock', ltrim($bin,'%'));
							}
							break;
					}
				}
			}
		}*/

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('entity_id', array(
          'header'    => Mage::helper('warehouse')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'entity_id',
		));
		
		$this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
        ));

		$this->addColumn('name', array(
                'header'=> Mage::helper('warehouse')->__('Name'),
                'index' => 'name',
		));
		
		$this->addColumn('upc', array(
                'header'=> Mage::helper('warehouse')->__('UPC'),
                'index' => 'upc',
		));

		return parent::_prepareColumns();
	}

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product_ids');

        $this->getMassactionBlock()->addItem('printA8460', array(
             'label'=> Mage::helper('catalog')->__('Print (1" x 2.625") Avery 8460'),
             'url'  => $this->getUrl('*/*/printA8460')
        ));

        $this->getMassactionBlock()->addItem('printA5163', array(
             'label'=> Mage::helper('catalog')->__('Print (2" x 4") Avery 5163'),
             'url'  => $this->getUrl('*/*/printA5163')
        ));
        
        $this->getMassactionBlock()->addItem('printA5168', array(
             'label'=> Mage::helper('catalog')->__('Print (3.5" x 5") Avery 5168'),
             'url'  => $this->getUrl('*/*/printA5168')
        ));
        
        $this->getMassactionBlock()->addItem('printTherm', array(
             'label'=> Mage::helper('catalog')->__('Print (4" x 6.75") Thermal'),
             'url'  => $this->getUrl('*/*/printTherm')
        ));
        
        $this->getMassactionBlock()->addItem('printCS', array(
             'label'=> Mage::helper('catalog')->__('Print Small Red Bin Card'),
             'url'  => $this->getUrl('*/*/printCS')
        ));
        
        $this->getMassactionBlock()->addItem('printCN', array(
             'label'=> Mage::helper('catalog')->__('Print Normal Bin Card'),
             'url'  => $this->getUrl('*/*/printCN')
        ));
        
       $this->getMassactionBlock()->addItem('printCL', array(
             'label'=> Mage::helper('catalog')->__('Print Large Green Bin Card'),
             'url'  => $this->getUrl('*/*/printCL')
        ));

        $this->getMassactionBlock()->addItem('printSmallUPC', array(
             'label'=> Mage::helper('catalog')->__('A8460 UPC labels'),
             'url'  => $this->getUrl('*/*/printSmallUPC')
        ));
        
        $this->getMassactionBlock()->addItem('printMediumUPC', array(
             'label'=> Mage::helper('catalog')->__('A5163 UPC labels'),
             'url'  => $this->getUrl('*/*/printMediumUPC')
        ));
        return $this;
    }

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}