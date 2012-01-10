<?php

class VO_Purchase_PriceController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('purchase/pricing')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Pricing'));
		return $this;
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	//Price List actions
	public function listAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function editListAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/price_list')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			$model->setData($data);

			Mage::register('price_list_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('purchase/pricing/list');

			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('purchase/prices_list_edit'))
			->_addContent($this->getLayout()->createBlock('purchase/prices_list_prices'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('List does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function saveListAction()
	{
		$id = $this->getRequest()->getParam('id');
		//Save the model
		$model = Mage::getModel('purchase/price_list');
		if (!empty($id))
		{
			$model->load($id);
		}
		$model->setcomment($this->getRequest()->getParam('comment'));
		$model->setname($this->getRequest()->getParam('name'));
		$model->setdate_modified(now());
		$model->save();

		//Make sure directory exists
		$path = Mage::getModuleDir('','VO_Purchase').DS.'Files'.DS;
		if (file_exists($path) == false)
		{
			mkdir($path);
		}
		$path .= 'List'. DS;
		if (file_exists($path) == false)
		{
			mkdir($path);
		}

		//Parse and add skus from csv
		if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
			try {
				$uploader = new Varien_File_Uploader('file');
				$uploader->setAllowedExtensions(array('csv','txt'));
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);
				$uploader->save($path, 'csvSkuDump.csv');
				$file = fopen($path.'csvSkuDump.csv', 'r');

				$fileHeader = fgetcsv($file);
				foreach ($fileHeader as $columnIndex => $header)
				{
					//Find which column the sku is from the headers
					if (!(strpos(strtoupper($header), 'SKU') === false))
					{
						$skuIndex = $columnIndex;
					}
				}
				if (isset($skuIndex))
				{
					while (($fileData = fgetcsv($file)) !== FALSE)
					{
						if (!empty($fileData[$skuIndex]))
						{
							$id = Mage::getModel('catalog/product')->getIdBySku($fileData[$skuIndex]);
							if (!empty($id))
							{
								$model->addProduct($id);
							}
							else
							{
								Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__($fileData[$skuIndex].' could not be found in the database.'));
							}
						}
					}
				}
				fclose($file);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		//Sku dump
		$dump = $this->getRequest()->getParam('dump');
		if (!empty($dump))
		{
			$data = Mage::getModel('utility/sku')->parse($dump,false);
			foreach ($data as $productId => $sku)
			{
				$model->addProduct($sku['model']);
			}
		}

		$this->_redirect('*/*/editList',array('id'=>$model->getId()));
	}

	public function newListAction() {
		$this->_forward('editList');
	}

	public function deleteListAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('purchase/price_list');
					
				$model->setId($this->getRequest()->getParam('id'))
				->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('List was successfully deleted'));
				$this->_redirect('*/*/list');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/editList', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/list');
	}

	//List item functions
	public function exportListCsvAction()
	{
		$list = Mage::getModel('purchase/price_list')->load($this->getRequest()->getParam('id'));
		$stuff = $list->getPricingCsvArray();
		$data = $stuff['data'];
		$headers = $stuff['headers'];
		unset($stuff);
		$CSV = Mage::getModel('utility/csv');
		$CSV->initialize($data, $headers);
		$this->_prepareDownloadResponse($list->getName().'.csv', $CSV->getContent(), $CSV->getContentType());
	}

	public function removeListItemAction()
	{
		$ids = $this->getRequest()->getParam('ids');
		foreach ($ids as $id)
		{
			$model = Mage::getModel('purchase/price_list_price')->load($id);
			$model->delete();
		}
		$this->_redirect('*/*/editList', array('id' => $this->getRequest()->getParam('list_id')));
	}

	public function newListProductAction()
	{
		$listId = $this->getRequest()->getParam('list_id');
		$this->loadLayout();
		$gridBlock = $this->getLayout()->createBlock('purchase/prices_list_prices_add');
		$gridBlock->listId = $listId;
		$this->_addContent($gridBlock);
		$this->renderLayout();
	}

	public function addListProductAction()
	{
		$list = Mage::getModel('purchase/price_list')->load($this->getRequest()->getParam('list_id'));
		foreach ($this->getRequest()->getParam('product') as $id)
		{
			$list->addProduct($id);
		}
		$this->_redirect('*/*/editList', array('id' => $list->getId()));
	}

	//Pricing tool functions
	public function loadAction()
	{
		$this->loadLayout();

		//$this->_addContent($this->getLayout()->createBlock('purchase/prices_plan_grid'));
		$this->renderLayout();
	}

	public function loadPricesAction()
	{
		$params = $this->getRequest()->getParams();
		$products = null;
		$category = null;
		switch ($params['type'])
		{
			case 'category':
				$category = $params['category'];
				break;
			case 'prefix':
				break;
			case 'single':
				$products = array($params['value']);
				break;
			case 'dump':
				$dump = $params['dump'];
				if (!empty($dump))
				{
					$data = Mage::getModel('utility/sku')->parse($dump,false);
					$products = array_keys($data);
				}
				break;
			default:
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('No prices could be loaded.'));
				$this->_redirect('*/*/load');
				return;
		}
		if (empty($products) && empty($category))
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('No prices could be loaded.'));
			$this->_redirect('*/*/load');
		}

		$this->_forward('new',null,null,array('product'=>serialize($products),'category'=>$category,'massaction_prepare_key'=>'product'));
		//$this->_redirect('*/*/new',array('product'=>serialize($products),'category'=>$category,'massaction_prepare_key'=>'product'));
	}

	public function newAction()
	{
		//This controller is responsible for choosing how to load up the new.phtml JSON.
		//The normal is to register a plan object, however the block function should be able to handle a PO, shipment
		//and straight array.
		$id     = $this->getRequest()->getParam('plan_id');
		$POId     = $this->getRequest()->getParam('po_id');
		$shipId     = $this->getRequest()->getParam('ship_id');
		$products     = $this->getRequest()->getParam('product');
		if (!is_array($products))
		{
			$products = unserialize($products);
		}
		$category = $this->getRequest()->getParam('category');

		if (isset($id) == TRUE )
		{
			$model  = Mage::getModel('purchase/price_plan')->load($id);
			Mage::register('purchase_price_group', $model);
			$this->_initAction()
			->renderLayout();
		}
		else if (isset($POId))
		{
			$model  = Mage::getModel('purchase/order')->load($POId);
			Mage::register('purchase_price_group', $model);
			$this->_initAction()
			->renderLayout();
		}
		else if (isset($shipId))
		{
			$model  = Mage::getModel('purchase/shipment')->load($shipId);
			Mage::register('purchase_price_group', $model);
			$this->_initAction()
			->renderLayout();
		}
		else if (isset($products))
		{
			/*foreach (explode(",", $products) as $productId)
			 {
				$data[] = $productId;
				}*/
			Mage::register('purchase_price_group', $products);
			$this->_initAction()
			->renderLayout();
		}
		else if(isset($category))
		{
			$category = Mage::getModel('catalog/category')->load($category);
			foreach ($category->getProductCollection() as $product)
			{
				$products[] = $product->getId();
				/*if($product->isSuper())
					{
					foreach ($product->getRelatedProductIds() as $subProduct)
					{
					$products .= $subProduct.',';
					}
					}*/
			}
			Mage::register('purchase_price_group', $products);
			$this->_initAction()
			->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('No argument!'));
			$this->_redirect('*/*/load');
		}
	}

	public function AJAXchangePriceAction()
	{
		$params = $this->getRequest()->getParams();
		$data = Zend_Json::decode($params['data']);
		$comment = $params['comment'];
		$updater = $params['updater'];
		$mode = $params['mode'];
		$collectionPlanId = (int)$params['col-plan'];
		$explanation = $params['explain'];
		$actual = (bool)$params['actual'];
		$report = array();
		$report['relational'] = false;

		//Get the object
		$price = Mage::getModel('purchase/price');
		if (isset($data['id']))
		{
			$price->load($data['id']);
		}
		else
		{
			//Things to set if it's totally new
			$price->setName($data['name']);
			$price->setSku($data['SKU']);
			$price->setProductId($data['prod_id']);
			$price->setpo_id($data['po_id']);
			$price->setship_id($data['ship_id']);
			$price->setOriginalLandedCost($price->getLandedCost());
			$price->setOriginalOEMCost($data['OEM-old']);
			$price->setOriginalDistributorCost($data['DC-old']);
			$price->setOriginalWholesaleCost($data['WC-old']);
			$price->setOriginalRetailCost($data['RC-old']);
		}

		//Things to set in any case
		$price->setComment($comment);
		$price->setupdater($updater);
		$price->setNewLandedCost($data['LC']);
		$price->setNewOEMCost($data['OEM-new']);
		$price->setNewDistributorCost($data['DC-new']);
		$price->setNewWholesaleCost($data['WC-new']);
		$price->setNewRetailCost($data['RC-new']);
		$price->setdata('date',now());
		$price->setaverage_margin($price->calculateAverageMargin());

		//Does plan exist?
		if (empty($data['plan_id']))
		{
			//Since he doesn't have a plan ID either he is new or the whole collection is:
			if (!empty($collectionPlanId))
			{
				//It's just him, HE WILL BE ASSIMILATED!
				$price->setplan_id($collectionPlanId);
				$plan = Mage::getModel('purchase/price_plan')->load($collectionPlanId);
			}
			else
			{
				//Plan? What plan? We need to create a new plan object for this guy.
				$plan = Mage::getModel('purchase/price_plan');
				$plan->setdate_created(now());
				$plan->save();
				$price->setplan_id($plan->getId());
			}
		}
		else
		{
			//Guess he does belong to a plan
			$price->setplan_id($data['plan_id']);
			$plan = Mage::getModel('purchase/price_plan')->load($data['plan_id']);
		}
		$plan->setexplanation($explanation);
		$plan->setupdater($updater);
		$plan->setdate_planned(now());
		if ($plan->isActive() == true)
		{
			$plan->setdate_activated(now());
		}
		$plan->save();
		$report['plan_id'] = $price->getplan_id();

		//Ave-margin
		//Change text

		//predictive?
		if ($mode == 'predict')
		{
			$price->setis_predictive(true);
		}

		//Effective?
		try {
			if ($actual == TRUE)
			{
				$product = $price->change();
				if ($product->getTypeId() == 'simple' && $product->loadParentProductIds())
				{
					$report['relational'] = 'child';
				}
				else if ($product->getTypeId() != 'simple')
				{
					$report['relational'] = 'parent';
				}
			}
			else
			{
				$price->save();
			}
			$report['id'] = $price->getId();
			$report['save'] = true;
		} catch (Exception $e) {
			$report['save'] = false;
		}

		//Report!
		echo Zend_Json::encode($report);
	}

	public function AJAXremovePriceAction()
	{
		$model = Mage::getModel('purchase/price')->load($this->getRequest()->getParam('price_id'));
		if (!$model->getActive())
		{
			$model->delete();
		}
	}

	public function AJAXrelationalInfoAction()
	{
		//This function will help the js construct a dialog representing what is going on:
		$type = $this->getRequest()->getParam('relation');
		$product = Mage::getModel('catalog/product')->load( $this->getRequest()->getParam('product_id'));
		$result = array('type'=>$type);
		if ($type == 'child')
		{
			foreach ($product->loadParentProductIds()->getData('parent_product_ids') as $parentId)
			{
				$parentProduct = Mage::getModel('catalog/product')->load($parentId);
				$collection = Mage::getModel('catalog/product_type_configurable')->getUsedProductCollection($parentProduct);
				//$collection->addAttributeToSelect('name');
				foreach ($collection as $sibling)
				{
					if($sibling->getId() != $product->getId())
					{
						$siblings[] = array('id'=>$sibling->getId(),'sku'=>$sibling->getSku());
					}
				}
				$data['parents'][] = array
				(
					'id'=>$parentId,
					'name'=>$parentProduct->getName(),
					'sku'=>$parentProduct->getSku(),
					'children'=>$siblings
				);
			}
		}
		else if ($type == 'parent')
		{
			$collection = Mage::getModel('catalog/product_type_configurable')->getUsedProductCollection($product);
			//$collection->addAttributeToSelect('name');
			foreach ($collection as $child)
			{
				$data['children'][] = array('id'=>$child->getId(),'sku'=>$child->getSku());
			}
		}
		$result['data'] = $data;
		echo Zend_Json::encode($result);
	}

	public function AJAXrelationalChangeAction()
	{
		$products = Zend_Json::decode(str_replace('"','',stripslashes($this->getRequest()->getParam('related'))));
		$data = Zend_Json::decode($this->getRequest()->getParam('change'));
		$source = Mage::getModel('purchase/price')->load($data['id']);
		//This keep track of the price id's that the JS will need to update.
		$inPlan = array();
		$needToRefresh = false;

		//It is possible that the item exists in the same plan already, load any canidates:
		$alreadyExisting = Mage::getModel('purchase/price')->getCollection()
		->addFieldToFilter('product_id', array('in'=>$products))
		->addFieldToFilter('plan_id', $data['plan_id']);

		//The plan must exist
		$plan = Mage::getModel('purchase/price_plan')->load($data['plan_id']);

		foreach ($alreadyExisting as $alreadyExists)
		{
			//Logic to load proper price change if it's already in the plan, just set new things.
			$alreadyExists->setNewLandedCost($data['LC']);
			$alreadyExists->setNewOEMCost($data['OEM-new']);
			$alreadyExists->setNewDistributorCost($data['DC-new']);
			$alreadyExists->setNewWholesaleCost($data['WC-new']);
			$alreadyExists->setNewRetailCost($data['RC-new']);
			$alreadyExists->setdata('date',now());
			$alreadyExists->setComment($source->getComment());
			if ($alreadyExists->getOldLandedCost() != $data['LC'])
			{
				$alreadyExists->setData('is_predictive',true);
			}

			$alreadyExists->change();

			$needToRefresh = true;
			$inPlan[] = $alreadyExists->getProductId();
		}
		$products = array_diff($products, $inPlan);

		foreach ($products as $product)
		{
			//Anything left must be constructed from scratch
			$additional = Mage::getModel('purchase/productadditional')->loadByProductId($product);
			$price = Mage::getModel('purchase/price');

			//Things to set if it's totally new, these need to come from something else.
			$price->setName($additional->getName());
			$price->setSku($additional->getSku());
			$price->setProductId($product);
			$price->setOriginalLandedCost($price->getLandedCost());
			$price->setOriginalOEMCost($additional->getOEMCost());
			$price->setOriginalDistributorCost($additional->getDistributorCost());
			$price->setOriginalWholesaleCost($additional->getWholesaleCost());
			$price->setOriginalRetailCost($additional->getRetailCost());

			//Things to set in any case
			$price->setupdater($plan->getupdater());
			if (!empty($data['LC']))
			{
				$price->setNewLandedCost($data['LC']);
			}
			else
			{
				$price->setNewLandedCost($price->getLandedCost());
			}
			$price->setNewOEMCost($data['OEM-new']);
			$price->setNewDistributorCost($data['DC-new']);
			$price->setNewWholesaleCost($data['WC-new']);
			$price->setNewRetailCost($data['RC-new']);
			$price->setdata('date',now());
			$price->setComment($source->getComment());

			$price->setplan_id($plan->getId());

			$price->change();
		}


		echo Zend_Json::encode(array($inPlan,$needToRefresh));
	}

	public function graphAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function viewAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/price')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('pricing_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('purchase/pricing');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Price Change'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('purchase/prices_view'));

			$this->renderLayout();

		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Price Change does not exist'));
			$this->_redirect('*/*/');
		}
	}

}