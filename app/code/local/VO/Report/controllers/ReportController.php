<?php
class VO_Report_ReportController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('report/complete'));
		$this->renderLayout();
	}

	public function downloadPricingAction()
	{
		$from = $this->getRequest()->getParam('from');
		$to = $this->getRequest()->getParam('to');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query = 'SELECT sku, value as name, -SUM(magnitude)
	FROM purchase_stock_movements
	INNER JOIN catalog_product_entity ON catalog_product_entity.entity_id = purchase_stock_movements.product_id
	LEFT OUTER JOIN catalog_product_entity_varchar ON catalog_product_entity_varchar.entity_id = purchase_stock_movements.product_id
	WHERE type = "Ordered" AND attribute_id = 60 ';
		if($from)
		{
			$query .= ' AND purchase_stock_movements.date >= "'.$from.'" ';
		}
		if($to)
		{
			$query .= ' AND purchase_stock_movements.date <= "'.$to.'" ';
		}
		$query .= ' GROUP BY sku';
		
		$data = $read->fetchAll($query);
		if(sizeof($data) > 0)
		{
		$CSV = Mage::getModel('utility/csv');
		$CSV->initialize($data, array('Sku','Name','Sales'));
		$this->_prepareDownloadResponse('data.csv', $CSV->getContent(), $CSV->getContentType());
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError("No data in that range");
			$this->_redirectError($this->getUrl('*/*/index'));
		}
	}
}