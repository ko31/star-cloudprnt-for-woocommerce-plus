<?php
	include_once('printer_queue.inc.php');

	class Star_CloudPRNT_Text_Plain_Job
	{
		const SLM_NEW_LINE_HEX = "0A";
		const SLM_SET_EMPHASIZED_HEX = "1B45";
		const SLM_CANCEL_EMPHASIZED_HEX = "1B46";
		const SLM_SET_LEFT_ALIGNMENT_HEX = "1B1D6100";
		const SLM_SET_CENTER_ALIGNMENT_HEX = "1B1D6101";
		const SLM_SET_RIGHT_ALIGNMENT_HEX = "1B1D6102";
		const SLM_FEED_FULL_CUT_HEX = "1B6402";
		const SLM_FEED_PARTIAL_CUT_HEX = "1B6403";
		const SLM_CODEPAGE_HEX = "1B1D74";
		
		private $printerMac;
		private $printerMeta;
		private $tempFilePath;
		private $printJobBuilder = "";
		
		public function __construct($printer, $tempFilePath)
		{
			$this->printerMeta = $printer;
			$this->printerMac = $printer["printerMAC"];
			$this->tempFilePath = $tempFilePath;
			$printJobBuilder = "";
			
			//print("<h4>Text Print Job ".$this->printerMac."</h4>");
			
		}
		
		private function str_to_hex($string)
		{
			$hex = '';
			for ($i = 0; $i < strlen($string); $i++)
			{
				$ord = ord($string[$i]);
				$hexCode = dechex($ord);
				$hex .= substr('0'.$hexCode, -2);
			}
			return strToUpper($hex);
		}
		
		public function set_text_emphasized()
		{
			//$this->printJobBuilder .= self::SLM_SET_EMPHASIZED_HEX;
		}
		
		public function cancel_text_emphasized()
		{
			//$this->printJobBuilder .= self::SLM_CANCEL_EMPHASIZED_HEX;
		}
		
		public function set_text_left_align()
		{
			//$this->printJobBuilder .= self::SLM_SET_LEFT_ALIGNMENT_HEX;
		}
		
		public function set_text_center_align()
		{
			//$this->printJobBuilder .= self::SLM_SET_CENTER_ALIGNMENT_HEX;
		}
		
		public function set_text_right_align()
		{
			//$this->printJobBuilder .= self::SLM_SET_RIGHT_ALIGNMENT_HEX;
		}
		
		public function set_codepage($codepage)
		{
			//$this->printJobBuilder .= self::SLM_CODEPAGE_HEX.$codepage;
		}
		
		public function add_nv_logo($keycode)
		{
			//$this->printJobBuilder .= "1B1C70".$keycode."00".self::SLM_NEW_LINE_HEX;
		}
		
		public function set_font_magnification($width, $height)
		{
		}
		
		public function add_hex($hex)
		{
			$this->printJobBuilder .= $hex;
		}
		
		public function add_text($text)
		{
			$this->printJobBuilder .= $this->str_to_hex($text);
		}
		
		public function add_text_line($text)
		{
			$this->printJobBuilder .= $this->str_to_hex($text).self::SLM_NEW_LINE_HEX;
		}
		
		public function add_new_line($quantity)
		{
			for ($i = 0; $i < $quantity; $i++)
			{
				$this->printJobBuilder .= self::SLM_NEW_LINE_HEX;
			}
		}
		
		public function printjob()
		{
			$fh = fopen($this->tempFilePath, 'w');
			fwrite($fh, hex2bin($this->printJobBuilder.self::SLM_NEW_LINE_HEX));
			//fwrite($fh, "Hello World\n\n");
			//fwrite($fh, hex2bin($this->printJobBuilder.self::SLM_FEED_PARTIAL_CUT_HEX));
			fclose($fh);
			star_cloudprnt_queue_add_print_job($this->printerMac, $this->tempFilePath, 1);
			unlink($this->tempFilePath);
		}
	}
?>