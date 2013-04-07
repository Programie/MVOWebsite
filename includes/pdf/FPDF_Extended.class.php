<?php
require_once "FPDF.class.php";

class FPDF_Extended extends FPDF
{
	private $headerCallback;
	private $headerCallbackParameters;
	
	public function SetHeaderCallback($callbackFunction, $parameters = null)
	{
		$this->headerCallback = $callbackFunction;
		$this->headerCallbackParameters = $parameters;
	}
	
	public function Header()
	{
		if (is_callable($this->headerCallback))
		{
			call_user_func($this->headerCallback, $this, $this->headerCallbackParameters);
		}
	}
}
?>