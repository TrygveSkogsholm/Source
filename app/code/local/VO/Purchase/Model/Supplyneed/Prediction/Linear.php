<?php

class VO_Purchase_Model_Supplyneed_Prediction_Linear extends VO_Purchase_Model_Supplyneed_Prediction
{
	public $coefficient = 1;
	public $offset = 0;
	
	public function getValue($x)
	{
		return ($this->coefficient*$x) + $this->offset;
	}
	
	public function getEquation()
	{
		return $this->coefficient.'x +'.$this->offset;
	}
	
	public function getDefiniteIntegral($from,$to)
	{
		//linear function is easy as hec. 1/2 base times height
		return round((($to-$from)*($this->getValue($to)-$this->getValue($from)))/2);
	}
	
	public function curveFit()
	{
		/*
		 * Thanks to the nature of linear functions we won't need to do the standard 
		 * trial and error here. We will use least squares here. 
		 */
		$n = sizeof($this->rawSales);
		
		$sumXY = 0;
		$sumX = 0;
		$sumY = 0;
		$sumSquares = 0;
		foreach($this->rawSales as $point)
		{
			$sumXY += ($point['x'] * $point['y']);
			$sumX += $point['x'];
			$sumY += $point['y'];
			$sumSquares += pow($point['x'], 2);
		}
		
		$slope = (($n*$sumXY) - ($sumX*$sumY))/(($n*$sumSquares) - pow($sumX, 2));
		$intercept = ($sumY - ($slope*$sumX))/$n;
		
		$this->coefficient = $slope;
		$this->offset = $intercept;
	}
}