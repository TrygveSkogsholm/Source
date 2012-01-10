<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VO_Warehouse_Block_Adminhtml_Inventory_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('WarehouseInventoryGrid');
        $this->_parentTemplate = $this->getTemplate();
    }

    protected function _prepareCollection()
    {		    
        $collection = Mage::getModel('catalog/product')
        	->getCollection()
        	->addAttributeToSelect('name')
        	->addAttributeToSelect('binlocation')
        	->addAttributeToSelect('type_id')
        	//->addAttributeToSelect('ordered_qty')
            //->addAttributeToSelect('status')
            ->joinTable(
                'cataloginventory/stock_item',
            	'product_id=entity_id',
                array('qty','is_in_stock'))
           	->addFieldToFilter('type_id','simple');
        	;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
                               
        $this->addColumn('sku', array(
            'header'=> Mage::helper('sales')->__('Sku'),
            'index' => 'sku'
        ));
        
        $this->addColumn('name', array(
            'header'=> Mage::helper('sales')->__('Name'),
            'index' => 'name'
        ));

        $this->addColumn('qty', array(
            'header'=> Mage::helper('warehouse')->__('Stock'),
            'index' => 'qty',
        	'type'=>'number',
            'align' => 'center'
        ));
        
        $this->addColumn('is_in_stock', array(
            'header'=> Mage::helper('warehouse')->__('In Stock'),
            'index' => 'is_in_stock',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('warehouse')->__('Yes'),
                '0' => Mage::helper('warehouse')->__('No'),
            ),
            'align' => 'center'
        ));


       	$this->addColumn('binlocation', array(
            'header'=> Mage::helper('warehouse')->__('Bin'),
            'index' => 'binlocation',
            'renderer' => 'VO_Warehouse_Block_Widget_Column_Renderer_Inventory_Bin',
            'align' => 'center'
        ));
        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return ''; //$this->getUrl('*/*/wishlist', array('_current'=>true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    

    /**
     * Définir l'url pour chaque ligne
     * permet d'accéder à l'écran "d'édition" d'une commande
     */
    public function getRowUrl($row)
    {
    	return '';
    }

}
