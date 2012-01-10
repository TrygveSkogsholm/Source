<?php

class VO_Purchase_Model_Supplyneed_Prediction_Cyclicgrowth extends VO_Purchase_Model_Supplyneed_Prediction
{
	//Function variables, this function will consist of a linear aproximation + a sin function for cyclic
	//The function here is Asin(Bx-C);
	public $linearFunction; // a linear prediction model
	public $termCoefficient = 1; //'A' controls amplitue of wave
	public $internalCoefficient = 1; //'B' controls frequency of wave
	public $internalOffset = 0; //'C' controls offset of wave
	
	/*
	 * for any given curve fit the most crucial variable for a fit is the offset, then wavelength 'B'
	 * finally amplitude.
	 * 
	 * In this particular case the fit should be redone at least three times.
	 */
	
	public function getValue($x)
	{
		return $this->linearFunction->getValue($x) + $this->termCoefficient*sin(($this->internalCoefficient*$x)-$this->internalOffset);
	}
	
	public function getEquation()
	{
		return number_format($this->coefficient).'x +'.number_format($this->offset);
	}
	
	public function getDefiniteIntegral($from,$to)
	{
		//Indefinite integral is -(A/B)cos(Bx-C) + 1/2 D x^2 + Ex + K, where K is the initial sales which is zero
		$A = &$this->termCoefficient;
		$B = &$this->internalCoefficient;
		$C = &$this->internalOffset;
		$D = &$this->linearFunction->coefficient;
		$E = &$this->linearFunction->offset;
		
		$endSum = -($A/$B)*cos(($B*$to)-$C) + 0.5*$D*(pow($to,2)) + $E*$to;
		$startSum = -($A/$B)*cos(($B*$from)-$C) + 0.5*$D*pow($from,2) + $E*$from;
		
		//To evaluate an integral simply take the value at to and subtract the value at from
		return $endSum - $startSum;
	}
	
	public function curveFit()
	{
		/*
		 * Alright, here's the plan, for each variable or term in the equation we will go through
		 * a while loop finding the best fit by modyfing the varible.
		 * 
		 * The modifiction will take the form of adding or subtracting an increment.
		 * 
		 * The addition vs subtraction is reffered to as direction.
		 * 
		 * Once all variables have found a best fit we set them and are ready to start outputing
		 * info.
		 * 
		 * In each iteration of a variable fit a change is made to the variable, if it improves
		 * the fit it will continue, otherwise the direction will reverse and the increment will
		 * reduce. The fit will 'wiggle' around the best fit like conserved harmonic motion.
		 * 
		 * There are three ways to exit the loop, a certain divergence is reached, a certain small
		 * incremnt, or a certain number of iterations have been completed.
		 */
		$this->linearFunction = Mage::getModel('purchase/supplyneed_prediction_linear',array('product_id'=>$this->product_id,'target'=>$this->target));
		$this->linearFunction->curveFit();
		
		$IOI = 1;
		$ICI = 1;
		$TCI = 1;
		for ($i = 0; $i < 8; $i++)
		{
			$IOI = $this->optimizeVariable($this->internalOffset,$IOI);
			$ICI = $this->optimizeVariable($this->internalCoefficient,$ICI);
			$TCI = $this->optimizeVariable($this->termCoefficient,$TCI);
		}
	}
}