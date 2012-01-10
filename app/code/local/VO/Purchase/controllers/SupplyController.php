<?php

class VO_Purchase_SupplyController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('purchase/supplyneeds')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Supply Needs'));
		return $this;
	}

	public function indexAction()
	{
		$this->_initAction()
		->renderLayout();
	}

	public function getAggregateSeriesAction()
	{
		$grouping = $this->getRequest()->getParam('group');
		$id = $this->getRequest()->getParam('id');
		
		$series = array();
		$data = array();
		$results = $this->getAggregateData($id,null,null,$grouping);
		/*
		 * Now the aggregation columns can be tricky, to deal with this we're going to have an index map
		 * of sorts, we will associate a period string with a integer, we will also check for an existing 
		 * map in the paremeters to make sure we keep the same index and place them in the proper places.
		 */
		$map = array();
		
		foreach ($results as $row)
		{
			$index = array_push($map, $row['period']);
			//(int)$row['timestamp']
			$data[] = array($index,(int)$row['sales']);
		}
		$series['data'] = $data;
		$series['options'] =  array('map'=>$map);
		echo Zend_Json::encode($series);
	}
	
	public function getPlainSeriesAction()
	{
		$id = $this->getRequest()->getParam('id');
		
		$series = array();
		$data = array();
		$results = $this->sqlQuery($id);
		foreach ($results as $row)
		{
			$data[] = array((int)$row['timestamp'],(int)$row['sales']);
		}
		$series['data'] = $data;
		//$series['options'];
		echo Zend_Json::encode($series);
	}

	public function getAggregateData($id,$from,$to,$groupBy = 'month')
	{
		$groupByClause = 'GROUP BY period';
		switch ($groupBy)
		{
			case 'day':
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
							    COUNT(date) AS movement_count,
							    CONCAT(MONTHNAME(date),\' \',DAYOFMONTH(date),\', \',CAST(YEAR(date) AS CHAR)) AS period,
							    SUM(-magnitude) AS sales';
				break;

			case 'week':
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
							    COUNT(date) AS movement_count,
							    CONCAT(WEEK(date),\' \',CAST(YEAR(date) AS CHAR)) AS period,
							    SUM(-magnitude) AS sales';
				break;

			case '2week':
				$groupByClause = 'GROUP BY ((52/2) * YEAR( date ) + FLOOR( WEEK( date ) /2 ))';
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
							    COUNT(date) AS movement_count,
							    ((52/2) * YEAR( date ) + FLOOR( WEEK( date ) /2 )) AS period,
							    SUM(-magnitude) AS sales';
				break;
					
			case 'month':
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
							    COUNT(date) AS movement_count,
							    CONCAT(MONTHNAME(date),\' \',CAST(YEAR(date) AS CHAR)) AS period,
							    SUM(-magnitude) AS sales';
				break;
					
			case 'quarter':
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
							    COUNT(date) AS movement_count,
							    CONCAT(QUARTER(date),\'/4 \',CAST(YEAR(date) AS CHAR)) AS period,
							    SUM(-magnitude) AS sales';
				break;
					
			case 'year':
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
							    COUNT(date) AS movement_count,
							    CAST(YEAR(date) AS CHAR) AS period,
							    SUM(-magnitude) AS sales';
				break;
					
			case 'lifetime':
				$groupByClause = 'GROUP BY sku';
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
							    COUNT(date) AS movement_count,
							    CAST(YEAR(date) AS CHAR) AS period,
							    SUM(-magnitude) AS sales';
				break;
					
			default:
				$fieldsString = 'ROUND(AVG(1000*UNIX_TIMESTAMP(date))) AS timestamp,
			    COUNT(date) AS movement_count,
			    CONCAT(MONTHNAME(date),\' \',CAST(YEAR(date) AS CHAR)) AS period,
			    SUM(-magnitude) AS sales';
				break;
		}

		$fromString = '';
		if($from)
		{
			if(is_numeric($from))
			{
				$fromString = 'AND date >= FROM_UNIXTIME('.($from/1000).') ';
			}
			else
			{
				$fromString = 'AND date >= "'.$from.'" ';
			}
		}

		$toString = '';
		if($to)
		{
			if(is_numeric($to))
			{
				$toString = 'AND date >= FROM_UNIXTIME('.($to/1000).') ';
			}
			else
			{
				$toString = 'AND date >= "'.$to.'" ';
			}
		}

		$idString = '';
		if (is_numeric($id))
		{
			$idString = 'AND product_id = '.$id.' ';
		}
		else
		{
			$idString = 'AND sku = "'.$id.'" ';
		}

		if(!empty($idString))
		{
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$query = 'SELECT '.$fieldsString.',sku
			FROM purchase_stock_movements
			LEFT OUTER JOIN catalog_product_entity ON catalog_product_entity.entity_id = product_id
			WHERE type = "Ordered" '.$idString.$fromString.$toString.'
			'.$groupByClause.'
			ORDER BY date;';
			$result = $read->fetchAll($query);
			return $result;
		}
	}

	public function sqlQuery($id,$from=false,$to=false,$groupBy = '',$fields = '*')
	{
		if ($fields == '*')
		{
			if (!empty($groupBy))
			{
				$fieldsString = '1000*UNIX_TIMESTAMP(date) AS timestamp,SUM(-magnitude),';
			}
			else
			{
				$fieldsString = '1000*UNIX_TIMESTAMP(date) AS timestamp,-magnitude AS sales,stockafter AS inventory,';
			}
		}
		elseif(is_array($fields))
		{
			foreach ($fields as $field)
			{
				$fieldsString .= $field.', ';
			}
		}

		$fromString = '';
		if($from)
		{
			if(is_numeric($from))
			{
				$fromString = 'AND date >= FROM_UNIXTIME('.($from/1000).') ';
			}
			else
			{
				$fromString = 'AND date >= "'.$from.'" ';
			}
		}

		$toString = '';
		if($to)
		{
			if(is_numeric($to))
			{
				$toString = 'AND date <= FROM_UNIXTIME('.($to/1000).') ';
			}
			else
			{
				$toString = 'AND date <= "'.$to.'" ';
			}
		}

		$idString = '';
		if (is_numeric($id))
		{
			$idString = 'AND product_id = '.$id.' ';
		}
		else
		{
			$idString = 'AND sku = "'.$id.'" ';
		}

		if(!empty($idString))
		{
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$query = 'SELECT '.$fieldsString.'catalog_product_entity.sku
			FROM purchase_stock_movements
			LEFT OUTER JOIN catalog_product_entity ON catalog_product_entity.entity_id = product_id
			WHERE type = "Ordered" '.$idString.$fromString.$toString.'
			ORDER BY date
			'.$groupBy.';';
			$result = $read->fetchAll($query);
			return $result;
		}
		return;
	}
	
	public function getSkuDataAction()
	{
		$id = $this->getRequest()->getParam('id');
		$product = Mage::getModel('catalog/product')->load($id);
		$response['data'] = array('sku'=>$product->getSku(),'name'=>$product->getName());
		if ($product->isSuper())
		{
			if($product->isSuperConfig())
			{
				$childCollection = Mage::getModel('catalog/product_type_configurable')->setProduct($product)->getUsedProductCollection()->addAttributeToSelect('name')->setOrder('sku','asc');
			}
			elseif($product->isGrouped())
			{
				$childCollection = Mage::getModel('catalog/product_type_grouped')->setProduct($product)->getUsedProductCollection()->addAttributeToSelect('name')->setOrder('sku','asc');
			}
			foreach($childCollection as $child)
			{
				$response['children'][$child->getId()] = array('type'=>'child','sku'=>$child->getSku(),'name'=>$child->getName());
			}
			$response['data']['type'] = 'parent';
		}
		elseif ($product->getTypeId() == 'simple')
		{
			$response['data']['type'] = 'simple';
		}
		echo Zend_Json::encode($response);
	}
	
	///Old functions

	public function getAggregateDataJSONAction()
	{
		//the similar function below attempts to do the same but in native google format. Send date data in microseconds
		//Sku or product_id
		$id = $this->getRequest()->getParam('id');

		//From and To must be mysql datetime format or microseconds from 1970
		$from = $this->getRequest()->getParam('from');
		$to = $this->getRequest()->getParam('to');

		//[hour,day,week,2week,month,quarter,year,lifetime]
		$groupBy = $this->getRequest()->getParam('by');
		$data = array();

		$result = $this->getAggregateData($id,$from,$to,$groupBy);
		if($result)
		{
			foreach($result as $row)
			{
				$data[] = array('timestamp'=>(int)$row['timestamp'],'movement_count'=>(int)$row['movement_count'],'period'=>$row['period'],'sales'=>(int)$row['sales']);
			}
		}
		echo Zend_Json::encode($data);
	}

	public function getDataTableJSONAction()
	{
		//Sku or product_id
		$ids = $this->getRequest()->getParam('id');

		//From and To must be mysql datetime format
		$from = $this->getRequest()->getParam('from');
		$to = $this->getRequest()->getParam('to');

		//[hour,day,week,2week,month,quarter,year,lifetime]
		$groupBy = $this->getRequest()->getParam('by');

		$data = array('cols'=>array(),'rows'=>array());
		$data['cols'][] = array('id'=>'period','label'=>$this->getRequest()->getParam('by-label'),'type'=>'number');

		//Place holder for construction:
		$rows = array();

		$index = 0;
		foreach ($ids as $id)
		{
			$index ++;
			$result = $this->getAggregateData($id,$from,$to,$groupBy);
			if($result)
			{
				$data['cols'][] = array('id'=>$result[0]['sku'],'label'=>$result[0]['sku'],'type'=>'number');
				//Data must be in google datatable format specified here http://code.google.com/apis/chart/interactive/docs/reference.html#dataparam
				foreach ($result as $row)
				{
					$rows[$row['period']][$index] = $row;
				}
			}
		}
		//This has the effect of joining rows from different queries to each other (different skus)
		//The row in the below is not the row above, but in fact an array of the above rows. Access via $row[sku][field]
		foreach($rows as $period => $rowGroup)
		{
			$cells = array();
			foreach ($rowGroup as $index => $sku)
			{
				$cells[$index] = array('v'=>(int)$sku['sales']);
			}
			$cells[0] = array('v'=>(int)$sku['timestamp'],'f'=>$period);
			$data['rows'][] = array
			(
			//cells object
				'c'=>$cells
			);
		}
		echo  Zend_Json::encode($data);
	}

	public function getDataAction()
	{
		$id = $this->getRequest()->getParam('id');

		//From and To must be mysql datetime format or microseconds from 1970
		$from = $this->getRequest()->getParam('from');
		$to = $this->getRequest()->getParam('to');

		$data = array();
		$result = $this->sqlQuery($id,$from,$to);
		if($result)
		{
			foreach($result as $row)
			{
				//$data[] = array('date'=>new Zend_Json_Expr('Date('.(int)$row['timestamp'].')'),'inventory'=>(int)$row['inventory'],'sales'=>(int)$row['sales']);
				$data[] = array('timestamp'=>(int)$row['timestamp'],'inventory'=>(int)$row['inventory'],'sales'=>(int)$row['sales']);
			}
		}
		//$options = array('enableJsonExprFinder'=>true);
		echo Zend_Json::encode($data,false);
	}

	public function getPredictedDataAction()
	{
		//Generate prediction
		$from = $this->getRequest()->getParam('from')/1000;
		$to = $this->getRequest()->getParam('to')/1000;
		$model = $this->getRequest()->getParam('model');
		$target = $this->getRequest()->getParam('target');
		$id = $this->getRequest()->getParam('id');
		
		if(empty($model))
		{
			$model = 'cyclicgrowth';
		}
		$prediction = Mage::getModel('purchase/supplyneed')->predictTrend($id,$target,$model);
		echo Zend_Json::encode(array('data'=>$prediction->generateData($from,$to),'equation'=>$prediction->getEquation(),'quality'=>$prediction->getQuality()));
	}

	public function getPossibleSkusAction()
	{
		///$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		//$query = '';
		//$result = $read->fetchAll($query);
		$options = array();
		$collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('name')
		->addAttributeToSelect('sku')
		->addFieldToFilter('type_id','simple');
		foreach ($collection as $product)
		{
			$options[] = array('label'=>$product->getSku(),'value'=>$product->getId());
			$options[] = array('label'=>$product->getName(),'value'=>$product->getId());
		}
		return Zend_Json::encode($options);
	}

	public function downloadCSVAction()
	{
		//echo $this->getRequest()->getParam('csvdata');
		//exit;
		return $this->_prepareDownloadResponse('data.csv', $this->getRequest()->getParam('csvdata'), 'text/csv');
	}
}