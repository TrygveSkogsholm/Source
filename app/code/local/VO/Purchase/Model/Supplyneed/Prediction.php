<?php

abstract class VO_Purchase_Model_Supplyneed_Prediction extends Mage_Core_Model_Abstract
{
	/*
	 * This class represents a specific function which is meant to predict sales data.
	 * It should be able to save itself to product-additional object to allow a cron schedule
	 * to warn us of needed buys. The actual function is a seperate class where this is more a wrapper
	 * or a parent.
	 *
	 * The x value is time in [Target] since 1970. I know it's not perfectly linear but it's close
	 * enough. The y value is sales. The intergral is total sales for a period, the derivative is
	 * whether sales are improving or going down. Second derivative is how fast the sales
	 * are improving or being lost.
	 *
	 * This is an abstract class and relies on children to fill in specific models (equations) to use.
	 *
	 * If you want to create a new math model simply create a new class and extend this, some functions are expected to be there
	 * getValue(x) <- return the y value for the x
	 * getDefiniteIntegral($from,$to) <- get the sum of sales for a certain period, try to find the mathmatic formula to save runtime
	 * getEquation() <- print the equation as a string
	 * curveFit() <- if you know a good way to curve fit to $this->data do it, otherwise just use the optimize function on your
	 * variables in a reasonable order a couple of times.
	 *
	 *	There may be some confusion thanks to JS vs mySQL unixtimestamp. For the record this file wants seconds not ms whenever
	 *	timestamp is referenced.
	 */
	public $product_id;
	public $lowestDivergence;

	//There are two raw datasets to consider, one for the running total (integral) and one for the straight data; we need both.
	public $rawSales;
	public $rawSalesTotal;

	//On the interval you need out of stock in stock dates

	//In some cases we may want to curve fit for the running total (the indefinite integral)
	public $integral = false;

	//target refers to the target period, it is an integer
	public $target = 1;

	//Abstract methods
	abstract function getValue($x);
	abstract function getDefiniteIntegral($from,$to);
	abstract function getEquation();
	abstract function curveFit();

	public function __construct($arguments)
	{
		$this->product_id = $arguments['product_id'];
		$this->setTarget($arguments['target']);
		$this->generateSourceData();
		parent::__construct();
	}

	public function setTarget($target)
	{
		if(!is_numeric($target))
		{
			switch ($target)
			{
				case 'day':
					$target = 86400;
					break;
				case 'week':
					$target = 604800;
					break;
				case '2week':
					$target = 1209600;
					break;
				case 'month':
					$target = 2629743.83;
					break;
				case 'quarter':
					$target = 7889231.5;
					break;
				case 'year':
					$target = 31556926;
			}
		}
		$this->target = $target;
		return;
	}

	/**
	 * This function calculates the divergence of the function from the data
	 * less is better. The squared distance mode is a common in statistics and defaults to true.
	 * Do not mix and match squared vs unsquared in the same optimization.
	 * @param boolean $squared
	 */
	public function checkFit($squared = true)
	{
		if (empty($this->data))
		{
			return null;
		}
		$totalDivergence = 0;
		foreach ($this->data as $realPoint)
		{
			$verticalDivergence = abs($realPoint['y'] - $this->getValue($realPoint['x']));
			if($squared)
			{
				$verticalDivergence *= $verticalDivergence;
			}
			$totalDivergence += $verticalDivergence;
		}
		return $totalDivergence;
	}

	//This function essentialy plays around with a single variable and moves it back and forth in smaller turns finding the best fit
	public function optimizeVariable(&$variable,$increment=1)
	{
		$lastDivergence = $this->checkFit() + 100000000;
		$bestFit = false;
		$direction = 1;

		$divergenceIdentity = 0;
		$count = 0;
		while($bestFit == false && $count < 50)
		{
			$count++;
			//Modify
			$variable += $increment*$direction;

			//Check fit
			$divergence = $this->checkFit();

			//Choose next change
			if($divergence > $lastDivergence)
			{
				/*
				 * Hold on, that didn't help, turn around and reduce the increment; we've got to be
				 * close since we just crossed ideal.
				 */
				$variable -= $increment*$direction;
				$divergence = $lastDivergence;
				//Ok,we're back where we started, what can we do to fix this? (get a less divergence)
				/*
				 * We either overshot or are going the wrong direction.
				 * If we overshot we need to try again with a smaller increment.
				 * If we are going the wrong direction we need to reverse it.
				 *
				 * To tell the difference we merely check to see if one or the other has the
				 * desired affect.
				 */
				$direction = -1*$direction;
				$increment *= 0.5;
			}
			elseif(round($lastDivergence,4) == round($divergence,4))
			{
				$divergenceIdentity ++;
			}

			if($divergenceIdentity == 3)
			{
				//Three strikes and your out (as good as it's going to get).
				$bestFit = true;
				$this->lowestDivergence = $divergence;
			}

			//Assign last fit
			$lastDivergence = $divergence;
		}
		return $increment;
	}

	public function getQuality()
	{
		return $this->lowestDivergence/sizeof($this->rawSales);
	}

	public function generateSourceData()
	{
		/*The source data must be aggregated via target info, but it is not important that jumps be respected.
		X will be in the units of the target i.e. months, years, days since 1970. So if x comes in as seconds
		and target is months then every x value should be divided by 2629743.83 and thus aggregated.
		*/
		if(!$this->rawSales)
		{
			$this->rawSales = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll('SELECT CEIL(UNIX_TIMESTAMP(`date`)/'.$this->target.') as `x`, -SUM(`magnitude`) as `y` FROM purchase_stock_movements WHERE type="Ordered" AND product_id = '.$this->product_id.' GROUP BY x ORDER BY date;');
			$totalSales = 0;
			foreach ($this->rawSales as $point)
			{
				//This process is equivalent to integrating but removes the troublesome aggregation dillema
				$totalSales += $point['y'];
				$this->rawSalesTotal[] =  array('x'=>$point['x'],'y'=>$totalSales);
			}
		}
		return $this->rawSales;
	}

	public function getRawSales()
	{
		return $this->rawSales;
	}

	public function getRawTotal()
	{
		return $this->rawSalesTotal;
	}

	/**
	 * This function generates from the data, because aggregation occurs on the client there should never be
	 * more than 1 datapoint per aggregation period. However a more complete picture of the function may be 
	 * requested which would require more points. These will be isolated only in exact mode.
	 * @param int $start in unix timestamp form (s not ms)
	 * @param int $end in unix timestamp form (s not ms)
	 * @param int $precision how often data points are wanted in multiples of the target.
	 */
	public function generateData($start,$end,$precision = 1)
	{
		$data = array();
		//Convert to target units
		$start = $start/$this->target;
		$end = $end/$this->target;
		for ($i = $start; $i < $end; $i += $precision)
		{
			/*Remember when I said in seconds, this is the exception; we will be spitting it out in ms
			That's going to be 500target(2i-1), remembering that we did ceil in the sql call and we want the time
			stamp to be the center of the period.*/
			$value = $this->getValue($i);
			if ($value < 0)
			{
				//There are no negative sales, bad selling products don't bring the rest down.
				$value = 0;
			}
			$data[] = array((500*$this->target*(2*$i - 1)),$value);
		}
		return $data;
	}

	/*
	 * As per the js strategy there will be functions here to output the data series as needed.
	 *
	 * There are several types of request:
	 * Aggregate - does not contain datetime info, but actual period names
	 * Prediction - use this prediction function
	 *
	 * The following functions for extracting data presume the format defined in http://people.iola.dk/olau/flot/API.txt
	 * raw data is merely an array [point,point,[x,y]] Since this represents a single sku only one series is returned at at time.
	 */
	public function getRawDataSeries()
	{

	}

	public function getRawTotalDataSeries()
	{

	}
}