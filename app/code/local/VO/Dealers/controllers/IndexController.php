<?php
class VO_Dealers_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
			
		/*
		 * Load an object by id
		 * Request looking like:
		 * http://site.com/<module>?id=15
		 *  or
		 * http://site.com/<module>/id/15
		 */
		/*
		 $<module>_id = $this->getRequest()->getParam('id');

		 if($<module>_id != null && $<module>_id != '')	{
			$<module> = Mage::getModel('<module>/<module>')->load($<module>_id)->getData();
			} else {
			$<module> = null;
			}
			*/

		/*
		 * If no param we load a the last created item
		 */
		/*
		 if($<module> == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$<module>Table = $resource->getTableName('<module>');

			$select = $read->select()
			->from($<module>Table,array('<module>_id','title','content','status'))
			->where('status',1)
			->order('created_time DESC') ;

			$<module> = $read->fetchRow($select);
			}
			Mage::register('<module>', $<module>);
			*/

			
		$this->loadLayout();
		$this->renderLayout();
	}

	public function displayAction()
	{
		//show front end
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Find Dealers'));
		$this->renderLayout();
	}

	public function editPostAction() {
		//account save
		if ($data = $this->getRequest()->getPost()) {
			$data['latitude'] = NULL;
			$model = Mage::getModel('dealers/dealers');
			$model->setData($data)
			->setId($this->getRequest()->getParam('dealer_id'));

			if ($model->hasDataChanges())
			{
				//Ask google again
				$model->setis_found(true);
			}

			$model->save();

			$this->_redirect('*/*/control');
		}

	}

	public function controlAction()
	{
		//the control page
		$this->loadLayout();

		$this->getLayout()->getBlock('head')->setTitle($this->__('My Stores'));

		if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
			$block->setRefererUrl($this->_getRefererUrl());
		}
		$this->renderLayout();
	}

	public function newAction()
	{
		//Just a page (the signup page)
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Become a dealer'));
		$this->renderLayout();
	}

	public function createAction()
	{
		/*
		 * This function is a bit different than the normal save
		 *  as it must encode some data first. It does so with the
		 *  encodeHours function in the dealer model which basicaly
		 *  echos most of the data but gives the right string.
		 */
		if ($data = $this->getRequest()->getPost())
		{
			$model = Mage::getModel('dealers/dealers');
			$saveData = $model->advancedSave($data);
			$model->setData($saveData);
			$model->save();

			//Make sure directory exists
			$path = Mage::getBaseDir().DS.'dealers'.DS.$model->getId().DS;
			if (file_exists($path) == false)
			{
				mkdir($path,0777,true);
			}

			$liabilityIncluded = false;
			if (isset($_FILES['liability']['name']) && $_FILES['liability']['name'] != '') {
				try {
					
					$uploader = new Varien_File_Uploader('liability');
					$uploader->setAllowedExtensions(array('bmp','jpg','jpeg','gif','png','pdf','psd','ai','odt','ods','odp','odg','doc','docx','pages'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$uploader->save($path, $_FILES['liability']['name']);
					$liabilityIncluded = true;
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}

			$resellersIncluded = false;
			if (isset($_FILES['resellers']['name']) && $_FILES['resellers']['name'] != '') {
				try {
					$uploader = new Varien_File_Uploader('resellers');
					$uploader->setAllowedExtensions(array('bmp','jpg','jpeg','gif','png','pdf','psd','ai','odt','ods','odp','odg','doc','docx','pages'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$uploader->save($path, $_FILES['resellers']['name']);
					$resellersIncluded = true;

				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}
				
			//alert of new dealer
			if(Mage::getStoreConfig('application/notification/email'))
			{
				if ($liabilityIncluded && $resellersIncluded)
				{
					$info = "attatched";
				}
				else
				{
					$info = "<b>not</b> attatched";
				}
				$html = 
				'
				<p>A comapany, '.$model->getname().'; has submited an application to become a dealer in '.$model->getcity().'.</p>
				<p>They have '.$info.' electronic copies of their liability insurance and a re-sellers permit.</p>
				'.$model->getemail();
				$mail = new Zend_Mail();
				$mail->setBodyHtml($html);
				$mail->addTo(Mage::getStoreConfig('application/notification/recipient'), 'Dealer Rep')
				->setSubject('New Dealer');
				$mail->send();
			}
		}
		$this->_redirect('*/*/new');
	}

	public function editHoursAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	public function saveHoursAction()
	{
		$this->loadLayout();
		$this->renderLayout();
		if ($data = $this->getRequest()->getPost())
		{
			$model = Mage::getModel('dealers/dealers');
			$hoursString = $model->encodeHours($data);
			$model->setData('hours',$hoursString)
			->setId($this->getRequest()->getParam('id'));
			$model->save();
		}
		//$this->_redirect('*/*/editHours')
		echo '
		<body onLoad="window.close()" >
		</body>
		';
	}

	public function saveLocationAction()
	{
		$model = Mage::getModel('dealers/dealers')->load($this->getRequest()->getParam('id'));
		$model->setLocation($this->getRequest()->getParam('latitude'),$this->getRequest()->getParam('longitude'));
		return;
	}

	//OLD STUFF:
	public function saveNewDataAction()
	{
		/*
		 * This function is designed to save the data that results from geocoding when
		 * it is neccesary, it should be called by some javascript on the display page.
		 */
		if ($this->getRequest()->getParams())
		{
			$location = trim($this->getRequest()->getParam('location'), "(");
			$location = trim($location, ")");
			$location = explode(',', $location);
			$latitude = $location[0];
			$longitude = $location[1];

			$data = array('latitude' => $latitude,'longitude'=>$longitude);

			$model = Mage::getModel('dealers/dealers');
			$model->setData($data)
			->setId($this->getRequest()->getParam('id'));
			$model->save();
		}
		$this->_redirect('*/*/display');
	}

	public function cantGeocodeAction()
	{
		/*
		 * This function is designed to save the data that results from geocoding when
		 * it is neccesary, it should be called by some javascript on the display page.
		 */
		$model = Mage::getModel('dealers/dealers')->load($this->getRequest()->getParam('id'));
		$model->setis_found(false);
		$model->save();
		return;
	}

	public function testAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			var_dump($data);
		}
		echo 'hello Welcome to test Action!';
	}
}