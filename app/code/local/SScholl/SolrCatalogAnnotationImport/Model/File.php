<?php /**************** Copyright notice ************************
 *  (c) 2011 Simon Eric Scholl <simon@sdscholl.de>
 *  All rights reserved
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 ***************************************************************/

class SScholl_SolrCatalogAnnotationImport_Model_File
	extends SScholl_SolrCatalogAnnotationImport_Model_Abstract
{

	const LOG_FILE				= 'sschollsolrcatalogannotationimport_solrimport_anno_file.log';
	const FILE_EXT_IMAGE		= 'jpg';
	const FILE_EXT_TEXT			= 'txt';
	static private $_types		= array (
// 		'AINFO',
// 		'BPROB', 'BPR01', 'BPR02', 'BPR03', 'BPR04', 'BPR05', 'BPR06', 'BPR07',
// 		'BPR08', 'BPR09', 'BPR10', 'BPR11', 'BPR12', 'BPR13', 'BPR14', 'BPR15',
// 		'BPR16', 'BPR17', 'BPR18', 'BPR19', 'BPR20', 'BPR21', 'BPR22', 'BPR23',
// 		'BPR24', 'BPR25', 'BPR26', 'BPR27', 'BPR28', 'BPR29', 'RUECK',
// 		'CBILD',
// 		'IVERZ',
// 		'KRITI',
		'KTEXT',
// 		'SALES',
	);
	static private $_idTypes	= array ('EN', 'LI',);
	
	private $_file				= null;
	private $_filePath			= null;
	
	private $_type				= null;
	private $_id				= null;
	private $_idType			= null;
	private $_format			= null;
	private $_text				= null;
	private $_imagePath			= null;
	private $_isImage			= null;
	private $_isText			= null;
	
	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->_file;
	}
	
	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return string
	 * 			LI or EN
	 */
	public function getIdType()
	{
		return $this->_idType;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		if (is_null($this->_text)) {
			if ($this->isText()) 
				$this->_text = $this->_loadText();
			else
				$this->_text = null;
		}
		return $this->_text;
	}
	
	/**
	 * returns the image path
	 * moves the image , mkdir if not exists 
	 * @return string
	 */
	public function getImagePath()
	{
		if (is_null($this->_imagePath)) {
			throw new Exception();
			//@todo add logic
		}
		return $this->_imagePath;
	}
	
	public function isImage()
	{
		if (is_null($this->_isImage))
			$this->_isImage =  
				strtolower($this->_format) === self::FILE_EXT_IMAGE
				&& (
					$this->_type === 'CBILD'
					|| substr_count($this->_type, 'BPR')
					|| $this->_type === 'RUECK'
				);
		return $this->_isImage;
	}
	
	public function isText()
	{
		if (is_null($this->_isText))
			$this->_isText =  
				strtolower($this->_format) === self::FILE_EXT_TEXT
				 && (
				 	$this->_type === 'AINFO'
					|| $this->_type === 'KRITI' 
					|| $this->_type === 'IVERZ' 
					|| $this->_type === 'SALES' 
					|| $this->_type === 'KTEXT'
				);
		return $this->_isText;
	}

	/**
	 * generates informations of the annotation by filename
	 * checks file exists
	 * @param string $file
	 * @param string $filePath
	 * @return boolean
	 */
	public function init($file, $filePath)
	{
		$this->_file		= $file;
		$this->_filePath	= $filePath;
		if (!file_exists($this->_filePath)) {
			Mage::log("file no longer exists: $this->_file", null, self::LOG_FILE);
			return false;
		}
		$fileParts = explode('.', $this->_file);
		if (!isset($fileParts[0], $fileParts[1]) || isset($fileParts[2])) {
			Mage::log("invalid format of file founded: $this->_file", null, self::LOG_FILE);
			return false;
		}
		$this->_format = $fileParts[1];
		$fileParts = explode('_', $fileParts[0]);
		if (!isset($fileParts[0], $fileParts[1], $fileParts[2], $fileParts[3]) || isset($fileParts[4])) {
			Mage::log("invalid format of file founded: $this->_file", null, self::LOG_FILE);
			return false;
		}
		$this->_idType	= $fileParts[0];
		$this->_id		= $fileParts[1];
		$this->_type	= $fileParts[3];
		if (!($this->isImage() || $this->isText())) {
			Mage::log("invalid format type of file founded: $this->_file", null, self::LOG_FILE);
			return false;
		}
		if (!in_array($this->_idType, self::$_idTypes)) {
			Mage::log("invalid id type of file founded: $this->_file", null, self::LOG_FILE);
			return false;
		}
		if (!in_array($this->_type, self::$_types)) {
			Mage::log("invalid type of file founded: $this->_file", null, self::LOG_FILE);
			return false;
		}
		return true;
	}
	
	/**
	 * returns text or the relative path to the image
	 * returns NULL on error
	 * @return Ambigous <string, NULL>
	 */
	public function getValue()
	{
		if ($this->isImage()) {
			return $this->getImagePath();
		} elseif ($this->isText()) {
			return $this->getText();
		}
		Mage::log("invalid file founded: $this->_file", null, self::LOG_FILE);
		return null;
	}
	
	private function _loadText()
	{
		$text = file_get_contents($this->_filePath);
		/*try {
			$text = mb_convert_encoding(
				$text,
				'UTF-8',
				mb_detect_encoding($text, 'Windows-1252, ISO-8859-15, ISO-8859-2, ISO-8859-1, UTF-8', true)
			);
			mb_detect_encoding($text, "UTF-8") === "UTF-8" ? : $text = utf8_encode($text);
			$text = iconv("UTF-8", "ISO-8859-1//IGNORE", $text);
			$text = iconv("ISO-8859-1", "UTF-8//IGNORE", $text);
		} catch (SScholl_Oniximporter_Model_Exception_NoticeIconv $n) {
			Mage::log('cant convert annotation' . $n->getMessage(), null, 'NoticeIconv.log');
			return null;
		}*/
		//TODO: add <br /> tags on line breaks, ticket #379
		/*$posStart	= mb_strripos($text, '<body>') + 6;
		$posEnd		= mb_strripos($text, '</body>');
		if (!$posEnd || $posStart <= 6 || ($posEnd - $posStart) < 0) {
			if (mb_strripos($text, '<html>') || mb_strripos($text, '<head>'))
				return null;
			$text = $text;
		}
		else {
			$text = substr($text, $posStart, $posEnd - $posStart);
		}*/
		return trim($text);
	}
	
	/**
	 * creates a folder recurive 
	 * returns true if folder already exists or is created
	 * @param string $folderPath
	 * @return boolean
	 */
	private function _mkdirRecurive($folderPath)
	{
		if (!file_exists($folderPath)) {
			if (mkdir($folderPath, 0777, true)) return true;
		} else {
			return true;
		}
		Mage::log('folder could not created: ' . $this->_filePath . " to $target", null, self::LOG_FILE);
		return false;
	}
	
	/**
	 * Copy image to Magmi import dir
	 * @return boolean
	 */
	private function _copyImage($target)
	{
		if (!rename($this->_filePath, $target)) {
			Mage::log('file could not moved: ' . $this->_filePath . " to $target", null, self::LOG_FILE);
			return false;
		}
		return true;
	}

}