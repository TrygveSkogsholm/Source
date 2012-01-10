<?php

class VO_Utility_Model_Csv extends Mage_Core_Model_Abstract
{
	private $content;

	/*
	 * data is a two dimensional array, no more no less.
	 * The two dimensions can be [row][column] (format true) or [column][row] (format false)
	 * however the former is most likely given the context of PHP arrays.
	 */
	private $data;

	/*
	 * Headers is an array of the fields to be included, by default (format true) it is the first row,
	 * or the first row of each column for (format false). The headers must be in order if they are passed manually.
	 */
	public $headers = array();

	public $format;
	private $delimeter;
	private $quote;

	/**
	 *
	 * This is the entry function for this model, it contains all the information to establish the CSV.
	 * Body can refer to content or an array itself.
	 * @param string|array $body
	 * @param bool $format
	 * @param array $headers
	 */
	public function initialize($body, $headers = NULL, $format = true, $delimeter = ',',$quote = '"')
	{
		$this->format = $format;
		$this->delimeter = $delimeter;
		$this->quote = $quote;

		if (!is_array($body))
		{	//We are loading up a csv string
			$this->content = trim($body);
			$this->generateData();
			if ($headers == NULL)
			{
				$this->headers = $this->getDefaultHeaders();
			}
			else
			{
				$this->headers = $headers;
			}
		}
		else
		{
			//We are loading up an array
			$this->data = $body;
			if ($headers == NULL)
			{
				$this->headers = $this->getDefaultHeaders();
			}
			else
			{
				$this->headers = $headers;
			}
			$this->generateContent();
		}

	}

	/**
	 * This function parses headers from the data, it also removes them since they aren't really data.
	 */
	private function getDefaultHeaders()
	{
		$headers = array();
		if ($this->format == true)
		{
			$headers = reset($this->data);
			unset($this->data[key($this->data)]);
		}
		else
		{
			foreach ($this->data as $index => $column)
			{
				$headers[] = reset($column);
				unset($this->data[$index][key($column)]);
			}
		}
		return $headers;
	}

	public function getContent()
	{
		return $this->content;
	}

	private function setContent()
	{

	}

	public function getCSVData()
	{
		return $this->data;
	}

	private function setCSVData()
	{

	}

	private function generateContent()
	{
		$content = '';
		//Headers
		$first = true;
		foreach ($this->headers as $header)
		{
			if ($first == false)
			{
				$content .= $this->delimeter;
			}
			$header = str_replace($this->quote, $this->quote.$this->quote , $header);
			$content .= $this->quote.$header.$this->quote;
			$first = false;
		}

		//Data line break
		$content .= "\r\n";

		//Data
		if ($this->format == true)
		{
			foreach ($this->data as $row)
			{
				//Write each column
				$first = true;
				foreach ($row as $field)
				{
					if ($first == false)
					{
						$content .= $this->delimeter;
					}
					$field = str_replace($this->quote, $this->quote.$this->quote , $field);
					$content .= $this->quote.$field.$this->quote;
					$first = false;
				}
				//End of row
				$content .= "\r\n";
			}
		}
		else
		{
			$lines = array();
			//assemble lines
			foreach ($this->data as $column)
			{
				foreach ($column as $rowId => $field)
				{
					if (!isset($lines[$rowId]))
					{
						$lines[$rowId] = '';
					}
					$field = str_replace($this->delimeter, $this->delimeter.$this->delimeter , $field);
					if ($lines[$rowId] == '')
					{
						$lines[$rowId] .=  $this->quote.$field.$this->quote;
					}
					else
					{
						$lines[$rowId] .= $this->delimeter.$this->quote.$field.$this->quote;
					}
				}
			}

			//write lines
			foreach ($lines as $line)
			{
				$content .= $line."\r\n";
			}
		}
		$this->content = $content;
	}

	private function generateData()
	{
		//I realize this may not be the fastest way to do this but it will be sturdy

		//These flags help assemble the proper format.
		$endField = false;
		$endRow = false;
		$inField = false;
		$enclosed = false;

		//This array in an intermediate, It is in [row][field] format.
		//As you can see it is in the proper shape for format == true, so we will only have to change
		//for format == false.
		$intermediate = array();

		//These variables keep a running record of the parsed data
		$row = array();
		$field = '';

		$charArray = str_split($this->content);
		for ($index = 0; $index < count($charArray); $index++)
		{
			/*
			 * So we are going to walk through this string, as far as we know we're just reading the first row
			 * there are only three things we care to notice:
			 * 1. new lines, this indicates that the row is over; no this does not mean we aren't doing line breaks
			 * in fields, but if we don't find it out here it's the end.
			 * 2. The delimeter, this means a new field
			 * 3. A quote (enclosing character) which is ignored, but allows stuff inside to be ignored, in that mode
			 * we only care about finding the end of the field. I will try to allow. As a tenative truth, nasty enclosing
			 * characters in the middle of a field may be ignored anyway, but certainly new lines or delimters will ruin
			 * everything.
			 */
			if (isset($charArray[$index + 1]))
			{
				if ($enclosed == false)
				{
					/*
					 * This is open space we should be on the lookout for delimeters, line breaks and enclosing characters.
					 * Note that since this is but one character only one of these can be executed, however the end of a row
					 * implies the end of a field
					 */
					if ($field != '')
					{
						//The field is not empty, we must be inside it.
						$inField = true;
					}
					else
					{
						$inField = false;
					}

					//Starting enclosed field?
					if ($charArray[$index] == $this->quote && $inField == false)
					{
						$enclosed = true;
						$inField = true;
					}

					//Done with a field?
					elseif ($charArray[$index] == $this->delimeter)
					{
						$endField = true;
					}

					//Done with a row?
					elseif (ord($charArray[$index]) == 10 || ord($charArray[$index+1]) == 10)
					{
						echo ' '.ord($charArray[$index]).' '.ord($charArray[$index+1]);
						/*
						 * Either this character or the next is \n, the three possibilities are:
						 * a single \n, a \r\n (where we are on \r now), or this is a normal character
						 * and the next is a single \n
						 */
						if (ord($charArray[$index]) == 13)
						{
							/*
							 * This is the start of a normal \r\n line break, the next character \n should be ignored
							 * so move the index forward one. And of course signal that the field and row is over.
							 */
							$endField = true;
							$endRow = true;
							$index++;
						}
						elseif (ord($charArray[$index]) == 10)
						{
							/*
							 * The first character is a new line same response without the index shift.
							 */
							$endField = true;
							$endRow = true;
						}
						else
						{
							//This character is not \n and it's not \r, it must be normal.
							$field .= $charArray[$index];
						}
					}

					//Well I guess it's just a normal part of the field.
					else
					{
						$field .= $charArray[$index];
					}
				}
				else
				{
					/*
					 * enclosed is true, all we care about here
					 * is the enclosing character that will escape us from this mode
					 */
					//Note that if it's a double delimeter we STILL don't care whuahhaaa
					if ($charArray[$index] == $this->quote)
					{
						if ($charArray[$index+1] != $this->quote)
						{
							//It's not a double just escape this field!
							$enclosed = false;
						}
						else
						{
							//It is a double, only write one delimeter by skipping the next character.
							$field .= $charArray[$index];
							$index++;
						}
					}
					else
					{
						$field .= $charArray[$index];
					}
				}
			}
			else
			{
				$field .= $charArray[$index];
				$endField = true;
				$endRow = true;
			}


			//Done with a field
			if ($endField == true)
			{
				// Add the field we have been working on
				$row[] = $field;
				//Reset the field variable
				$field = '';
				//Turn off new field until we find another unenclosed delimeter
				$endField = false;
				$inField = false;
			}

			//Done with a row
			if ($endRow == true)
			{
				//Add the row we have been working on to the data array
				$intermediate[] = $row;
				//Reset the row variable
				$row = array();
				//Turn off new row until we find another unenclosed line break
				$endRow = false;
			}
		}
		//We have the intermediate populated, now to convert if required
		if ($this->format == false)
		{
			foreach ($intermediate as $rowId => $row)
			{
				foreach ($row as $columnId => $field)
				{
					$this->data[$columnId][$rowId] = $field;
				}
			}
		}
		else
		{
			$this->data = $intermediate;
		}
	}

	public function getContentType()
	{
		return 'text/csv';
	}
}