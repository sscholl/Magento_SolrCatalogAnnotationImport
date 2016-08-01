<?php /**************** Copyright notice ************************
 *  (c) 2011 Simon Eric Scholl <simon@sdscholl.de>
 *  All rights reserved
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 ***************************************************************/

class SScholl_SolrCatalogAnnotationImport_Model_Zip
	extends SScholl_SolrCatalogAnnotationImport_Model_Abstract
{

	const LOG_FILE	= 'sschollsolrcatalogannotationimport_solrimport_anno_zip.log';
	const LOCK		= '.lock';

	private $_lock = false;
	private $_folder = null;

	/**
	 * sets the paths
	 * @param string $fileName
	 */
	public function __construct($fileName)
	{
		parent::__construct();
		$this->setData(
			array (
				'file_name'			=> $fileName,
				'path'				=> $this->_config()->getImportPath($fileName),
				'path_lock'			=> $this->_config()->getImportPath(self::LOCK . $fileName),
				'path_converted'	=> $this->_config()->getImportedPath($fileName),
			)
		);
		$this->_lock = (boolean) strstr($this->getFileName(), self::LOCK);
		return $this;
	}

	public function extract()
	{
		if (
			is_dir($this->getPathConvert())
			|| $this->_isLocked()
			|| !stristr($this->getFileName(), '.zip')
			|| !file_exists($this->getPath())
		) {
			return false;
		}
		if (!rename($this->getPath(), $this->getPathLock())) return false;
		$this->_log(' extract to ' . $this->getFolder()->getPath());
		if ($this->_extract()) return true;
		$this->_log(' could not extracted to ' . $this->getFolder()->getPath());
		return false;
	}

	/**
	 * @return SScholl_SolrCatalogAnnotationImport_Model_Folder
	 */
	public function getFolder()
	{
		if (is_null($this->_folder))
			$this->_folder = Mage::getModel(
				'sschollsolrcatalogannotationimport/folder',
				$this->_config()->getImportPath(
					str_ireplace('.zip', '', 'extract' . DS . $this->getFileName())
				)
			);
		return $this->_folder;
	}

	public function moveConvert()
	{
		return rename($this->getPathLock(), $this->getPath());
	}

	public function moveConverted()
	{
		return rename($this->getPathLock(), $this->getPathConverted());
	}

	private function _isLocked()
	{
		return $this->_lock;
	}

	private function _extract() {
		$zip = new ZipArchive;
		$res = $zip->open($this->getPathLock());
		if ($res !== true) return false;
		$zip->extractTo($this->getFolder()->getPath());
		$zip->close();
		if (!is_dir($this->getFolder()->getPath())) false;
		return true;
	}

	private function _log($string)
	{
		Mage::log($this->getFileName() . $string, null, self::LOG_FILE);
	}

}