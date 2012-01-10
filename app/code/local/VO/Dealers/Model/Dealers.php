<?php

class VO_Dealers_Model_Dealers extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('dealers/dealers');
	}

	public function getOptionArray()
	{
		$options = array('Frame Builder'=>'Frame Builder','Manufacturer'=>'Manufacturer','Dealer'=>'Dealer');
		return $options;
	}

	public function doesExist($customerID)
	{
		$flag = false;
		$collection = $this->getCollection()
		->addFieldToFilter('account_id', $customerID);
		foreach ($collection as $item)
		{$flag = true;}
		return $flag;
	}

	public function howMany($customerID)
	{
		$count = 0;
		$collection = $this->getCollection()
		->addFieldToFilter('account_id', $customerID);
		foreach ($collection as $item)
		{++$count;}
		return $count;
	}

	public function isFound()
	{
		return $this->getis_found();
	}

	public function setLocation($latitude,$longitude)
	{
		$this->setlatitude($latitude);
		$this->setlongitude($longitude);
		$this->setis_found(true);
		$this->save();
		return;
	}

	public function getFiles()
	{
		//returns the full links in array form
		$path = Mage::getBaseDir().DS.'dealers'.DS.$this->getId();
		if (is_dir($path) && $this->getId())
		{
			$handle = opendir($path);
			$files = array();
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
					$files[] = array('name'=>$file,'path'=>Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'dealers'.DS.$this->getId().DS.$file);
				}
			}
			return $files;
		}
		else
		{
			return false;
		}
	}
	
	public function getCustomer()
	{
		return Mage::getModel('customer/customer')->load($this->getData('account_id'));
	}
	
	public function approve()
	{
		$customer = $this->getCustomer();
		$customer->setData('group_id',2);
		$customer->save();
		$this->setData('is_approved',true);
		$this->save();
	}
	
	public function isApproved()
	{
		return $this->getData('is_approved');
	}

	public function getDefaults($id)
	{
		$customer = Mage::getModel('customer/customer')->load($id);
		$data = array();

		if($address = $customer->getPrimaryBillingAddress())
		{
			$data['company']=$address->getCompany();
			$data['address']=$address->getStreetFull();
			$data['zip']=$address->getPostcode();
			$data['state']=$address->getRegion();
			$data['city']=$address->getCity();
			$data['country']=$address->getCountry();
			$data['phone']=$address->getTelephone();
		}
		else
		{
			$data['company']='';
			$data['address']='';
			$data['zip']='';
			$data['state']='';
			$data['city']='';
			$data['country']='';
			$data['phone']='';
		}
			
		$data['email']=$customer->getEmail();
		return $data;
	}

	public function advancedSave($data = array())
	{
		$customerID = $data['account_id'];

		//If it's not the first assume it's not primary
		if ($this->howMany($customerID) >= 1)
		{
			$isPrimary = 0;
		}
		else
		{
			$isPrimary = 1;
		}
			
		if (isset($data['is_displayed_check']) && $data['is_displayed_check'] == 'on')
		{
			$isDisplayed = true;
		}
		else
		{
			$isDisplayed = false;
		}

		$region = Mage::getModel('directory/region')->load($data['region_id']);
		$regionName = $region->getcode();
		if(isset($regionName) == true)
		{
			$state = $regionName;
		}
		else
		{
			$state = $data['region'];
		}

		$hoursString = $this->encodeHours($data);

		$saveData = array();
			

		$saveData['account_id'] = $customerID;
		$saveData['is_primary'] = $isPrimary;
		$saveData['name'] = $data['name'];
		$saveData['is_displayed'] = $isDisplayed;
		$saveData['address'] = $data['address'];
		$saveData['city'] = $data['city'];
		$saveData['description'] = $data['description'];
		$saveData['country'] = $data['country_id'];
		$saveData['state'] = $state;
		$saveData['zip'] = $data['zip'];
		$saveData['phone'] = $data['phone'];
		$saveData['email'] = $data['email'];
		$saveData['website'] = $data['website'];
		$saveData['type'] = $data['type'];
		$saveData['hours'] = $hoursString;
			
		return $saveData;
	}

	public function encodeHours($data = array())
	{
		//Hours
		//First make sure custom field is blank
		if ($data['hoursType'] == 1)
		{
			$hoursString = '';

			//marks it as a non-custom string
			$hoursString .= '[]';

			if (isset($data['sun_check']) == true && $data['sun_check'] == 'on' && $data['sun_from'] != '' && $data['sun_to'] != '')
			{
				$hoursString .= 'sun';
				$hoursString .= ',';
				$hoursString .= $data['sun_from'];
				$hoursString .= ' '.$data['sun_from_p'];
				$hoursString .= ',';
				$hoursString .= $data['sun_to'];
				$hoursString .= ' '.$data['sun_to_p'];
				$hoursString .= ';';
			}

			if (isset($data['mon_check']) == true && $data['mon_check'] == 'on' && $data['mon_from'] != '' && $data['mon_to'] != '')
			{
				$hoursString .= 'mon';
				$hoursString .= ',';
				$hoursString .= $data['mon_from'];
				$hoursString .= ' '.$data['mon_from_p'];
				$hoursString .= ',';
				$hoursString .= $data['mon_to'];
				$hoursString .= ' '.$data['mon_to_p'];
				$hoursString .= ';';
			}

			if (isset($data['tue_check']) == true && $data['tue_check'] == 'on' && $data['tue_from'] != '' && $data['tue_to'] != '')
			{
				$hoursString .= 'tue';
				$hoursString .= ',';
				$hoursString .= $data['tue_from'];
				$hoursString .= ' '.$data['tue_from_p'];
				$hoursString .= ',';
				$hoursString .= $data['tue_to'];
				$hoursString .= ' '.$data['tue_to_p'];
				$hoursString .= ';';
			}

			if (isset($data['wed_check']) == true && $data['wed_check'] == 'on' && $data['wed_from'] != '' && $data['wed_to'] != '')
			{
				$hoursString .= 'wed';
				$hoursString .= ',';
				$hoursString .= $data['wed_from'];
				$hoursString .= ' '.$data['wed_from_p'];
				$hoursString .= ',';
				$hoursString .= $data['wed_to'];
				$hoursString .= ' '.$data['wed_to_p'];
				$hoursString .= ';';
			}

			if (isset($data['thu_check']) == true && $data['thu_check'] == 'on' && $data['thu_from'] != '' && $data['thu_to'] != '')
			{
				$hoursString .= 'thu';
				$hoursString .= ',';
				$hoursString .= $data['thu_from'];
				$hoursString .= ' '.$data['thu_from_p'];
				$hoursString .= ',';
				$hoursString .= $data['thu_to'];
				$hoursString .= ' '.$data['thu_to_p'];
				$hoursString .= ';';
			}

			if (isset($data['fri_check']) == true && $data['fri_check'] == 'on' && $data['fri_from'] != '' && $data['fri_to'] != '')
			{
				$hoursString .= 'fri';
				$hoursString .= ',';
				$hoursString .= $data['fri_from'];
				$hoursString .= ' '.$data['fri_from_p'];
				$hoursString .= ',';
				$hoursString .= $data['fri_to'];
				$hoursString .= ' '.$data['fri_to_p'];
				$hoursString .= ';';
			}

			if (isset($data['sat_check']) == true && $data['sat_check'] == 'on' && $data['sat_from'] != '' && $data['sat_to'] != '')
			{
				$hoursString .= 'sat';
				$hoursString .= ',';
				$hoursString .= $data['sat_from'];
				$hoursString .= ' '.$data['sat_from_p'];
				$hoursString .= ',';
				$hoursString .= $data['sat_to'];
				$hoursString .= ' '.$data['sat_to_p'];
				$hoursString .= ';';
			}

			if ($hoursString == '[]')
			{
				$hoursString = '';
				$hoursString = $data['hourstext'];
			}
		}
		else if ($data['hoursType'] == 2)
		{
			$hoursString = $data['hourstext'];
		}
		else
		{ $hoursString = '';}
		return $hoursString;
	}

	public function getHoursHTML($id)
	{
		$this->load($id);
		$hours = $this->gethours();
			
		//Check if it is custom or not.
		if(strpbrk($hours,'[]') == true)
		{
			$entries = $this->decodeHours($hours);
			$html = '<table>';
			foreach ($entries as $entry)
			{
				$html .= '<tr>';
				$html .= '<th>';
				$html .= $entry['entry'];
				$html .= '</th>';
				$html .= '<td>'.$entry['from'].' to '.$entry['to'];
				$html .= '</td></tr>';
			}
			$html .= '</table>';
		}
		else
		{
			$html = $hours;
		}
			
		return $html;
	}

	public function decodeHours($hours, $returnType = 'entries')
	{
		//cut off the marker
		$hours = ltrim($hours,'[]');
		//Let's get some variables out of this
		$daysStrings = explode(';',$hours);
		/*
		 * Ok now we have an array of day,from,to;
		 * What we want to do now is do a foreach and parse everything to the
		 * variables I know they can be (another array I know but with keys!)
		 */
		foreach ($daysStrings as $dayString)
		{
			$dayName = substr($dayString,0,3);
			switch ($dayName) {
				case 'sun':
					$sunString = $dayString;
					break;
				case 'mon':
					$monString = $dayString;
					break;
				case 'tue':
					$tueString = $dayString;
					break;
				case 'wed':
					$wedString = $dayString;
					break;
				case 'thu':
					$thuString = $dayString;
					break;
				case 'fri':
					$friString = $dayString;
					break;
				case 'sat':
					$satString = $dayString;
					break;
			}
		}

		$Week = array();
		//Create the array
		if (isset($sunString))
		{
			list($name,$from,$to) = explode(',',$sunString);
			$Sunday = array('name'=>'Sunday','from'=>$from,'to'=>$to);
			$Week[] = $Sunday;
		}
		else
		{$Sunday = null;}
		if (isset($monString))
		{
			list($name,$from,$to) = explode(',',$monString);
			$Monday = array('name'=>'Monday','from'=>$from,'to'=>$to);
			$Week[] = $Monday;
		}
		else
		{$Monday = null;}

		if (isset($tueString))
		{
			list($name,$from,$to) = explode(',',$tueString);
			$Tuesday = array('name'=>'Tuesday','from'=>$from,'to'=>$to);
			$Week[] = $Tuesday;
		}
		else
		{$Tuesday = null;}

		if (isset($wedString))
		{
			list($name,$from,$to) = explode(',',$wedString);
			$Wednesday = array('name'=>'Wednesday','from'=>$from,'to'=>$to);
			$Week[] = $Wednesday;
		}
		else
		{$Wednesday = null;}

		if (isset($thuString))
		{
			list($name,$from,$to) = explode(',',$thuString);
			$Thursday = array('name'=>'Thursday','from'=>$from,'to'=>$to);
			$Week[] = $Thursday;
		}
		else
		{$Thursday = null;}
		if (isset($friString))
		{
			list($name,$from,$to) = explode(',',$friString);
			$Friday = array('name'=>'Friday','from'=>$from,'to'=>$to);
			$Week[] = $Friday;
		}
		else
		{$Friday = null;}
		if (isset($satString))
		{
			list($name,$from,$to) = explode(',',$satString);
			$Saturday = array('name'=>'Saturday','from'=>$from,'to'=>$to);
			$Week[] = $Saturday;
		}
		else
		{$Saturday = null;}

		$entries = array();
		$flag = true;
		$count = count($Week);
		$q = 0;
		for ($i = 0; $i < $count; ++$i)
		{
			//First Entry
			if($flag == true)
			{
				$entries[$q] = array('entry'=>$Week[$i]['name'],'from'=>$Week[$i]['from'],'to'=>$Week[$i]['to']);
				$flag = false;
			}
			//Is there a tomorrow?
			if(isset($Week[$i+1]))
			{
				//Check the next day if it's the same jolly good else create a new entry.
				if ($Week[$i]['from'] == $Week[$i+1]['from'] && $Week[$i]['to'] == $Week[$i+1]['to'])
				{
				}
				else
				{
					if ($entries[$q]['entry'] != $Week[$i]['name'])
					{$entries[$q]['entry'] .= ' - '.$Week[$i]['name'];}
					++$q;
					$entries[$q] = array('entry'=>$Week[$i+1]['name'],'from'=>$Week[$i+1]['from'],'to'=>$Week[$i+1]['to']);
				}
			}
			else
			{
				//Well if there isn't this is the end of our list
				if ($entries[$q]['entry'] != $Week[$i]['name'])
				{$entries[$q]['entry'] .= ' - '.$Week[$i]['name'];}
			}
		}
		if ($returnType == 'entries')
		{return $entries;}
		else if ($returnType == 'enumerated')
		{
			//$enumerated = array();
			//foreach ($daysStrings as $dayString)
			//{
			//$list = explode(',',$dayString);
			//$dayName = $list[0];
			//$fp = substr($list[1],0,-2);
			//$f = str_replace($tp,'',$list[1]);
			//$tp = substr($list[2],0,-2);
			//$t = str_replace($tp,'',$list[2]);
			//$enumerated[$dayName] = array('from'=>$f,'fromp'=>$fp,'to'=>$t,'top'=>$tp);
			//}
			return $Week;
		}
	}
}