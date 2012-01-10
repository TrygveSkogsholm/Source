<?php
class VO_Warehouse_Block_Adminhtml_Ranges_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('rangeOrdersGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('warehouse/print')->getCollection()
				->join('sales/order',
			        'id=entity_id',
		        	'increment_id')
				->addFieldToFilter('range_id',Mage::registry('range_data')->getId());
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('increment_id', array(
          'header'    => Mage::helper('warehouse')->__('ID'),
          'index'     => 'increment_id'
		));

		return parent::_prepareColumns();
	}

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('reprint', array(
             'label'=> Mage::helper('warehouse')->__('Reprint'),
             'url'  => $this->getUrl('*/*/reprint')
        ));
        return $this;
    }

	public function getRowUrl($row)
	{
		return $this->getUrl('grandcru_admin/sales_order/view', array('order_id' => $row->getId()));
	}
}