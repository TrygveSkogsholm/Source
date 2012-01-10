<?php
class VO_Dealers_Block_Dealers extends Mage_Directory_Block_Data
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getDealerJson()
    {
    	$collection = Mage::getModel('dealers/dealers')->getCollection()
    	->addFieldToFilter('is_displayed',1)
    	->addFieldToFilter('is_approved',1)
    	->addFieldToFilter('type',array('neq'=>'Frame Builder'));
		foreach($collection as $dealer)
		{
			/*
			type
			name
			state
			zip
			city
			phone
			email
			website
			lat
			lng
			address
			id
			country
			hours
			description
			*/
			if(strlen($dealer->getcountry()) == 2)
			{
				$countryName = Mage::getModel('directory/country')->loadByCode($dealer->getcountry());
				$countryName = $countryName->getName();
			}
			else
			{
				$countryName = $dealer->getcountry();
			}
			
			$JSON[$dealer->getdealer_id()] = array
			(
				'type'=>$dealer->gettype(),
				'name'=>$dealer->getname(),
				'state'=>$dealer->getstate(),
				'zip'=>$dealer->getzip(),
				'city'=>$dealer->getcity(),
				'phone'=>$dealer->getphone(),
				'email'=>$dealer->getemail(),
				'website'=>$dealer->getwebsite(),
				'latitude'=>$dealer->getlatitude(),
				'longitude'=>$dealer->getlongitude(),
				'address'=>$dealer->getaddress(),
				'id'=>$dealer->getdealer_id(),
				'country'=>$countryName,
				'hours'=>$dealer->getHoursHTML($dealer->getdealer_id()),
				'description'=>$dealer->getdescription(),
				'found'=>(int)$dealer->isFound()
			);
		}
		return $JSON; //so that we can build the table from it too.
		//return Zend_Json::encode($JSON);
    }
}