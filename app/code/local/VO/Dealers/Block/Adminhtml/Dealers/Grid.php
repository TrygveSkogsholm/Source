<?php
class VO_Dealers_Block_Adminhtml_Dealers_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
		$collection = Mage::getModel('dealers/dealers')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('account_id', array(
          'header'    => Mage::helper('dealers')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'account_id',
		));

		
       $this->addColumn('is_approved', array(
            'header'=> Mage::helper('dealers')->__('Approved'),
 		    'width'     => '80',
            'index' => 'is_approved',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('Pending'),
            ),
            'align' => 'center'
        ));
        
		$this->addColumn('name', array(
          'header'    => Mage::helper('dealers')->__('Store Name'),
          'align'     =>'left',
          'index'     => 'name',
		));
		
		$this->addColumn('name', array(
          'header'    => Mage::helper('dealers')->__('Store Name'),
          'align'     =>'left',
          'index'     => 'name',
		));
		
		$this->addColumn('city', array(
          'header'    => Mage::helper('dealers')->__('City'),
          'align'     =>'left',
          'index'     => 'city',
		));
		$this->addColumn('state', array(
          'header'    => Mage::helper('dealers')->__('State'),
          'align'     =>'left',
          'index'     => 'state',
		));
		$this->addColumn('country', array(
          'header'    => Mage::helper('dealers')->__('Country'),
          'align'     =>'left',
          'index'     => 'country',
		));
		$this->addColumn('email', array(
          'header'    => Mage::helper('dealers')->__('Email'),
          'align'     =>'left',
          'index'     => 'email',
		));
						
		$this->addColumn('type', array(
          'header'    => Mage::helper('dealers')->__('Type'),
          'align'     =>'left',
          'index'     => 'type',
		  'type'  => 'options',
          'options' => Mage::getSingleton('dealers/dealers')->getOptionArray(),
		));
		
        $this->addColumn('is_primary', array(
            'header'=> Mage::helper('dealers')->__('Primary'),
       		'width'     => '80',
            'index' => 'is_primary',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));
       $this->addColumn('is_displayed', array(
            'header'=> Mage::helper('dealers')->__('Visible'),
 		    'width'     => '80',
            'index' => 'is_displayed',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
        $this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));
        
		return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}