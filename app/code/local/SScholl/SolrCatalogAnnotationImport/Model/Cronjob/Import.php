<?php /**************** Copyright notice ************************
 *  (c) 2011 Simon Eric Scholl <simon@sdscholl.de>
 *  All rights reserved
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 ***************************************************************/

class SScholl_SolrCatalogAnnotationImport_Model_Cronjob_Import
	extends SScholl_OnixImport_Model_Cronjob_Abstract
{
	const LOG_FILE			= 'sschollsolrcatalogannotationimport_solrimport_anno_cron.log';
	const LOG_FILE_IMPORTED	= 'sschollsolrcatalogannotationimport_solrimport_anno_cron_imported.log';

	protected $_coincidentProcesses = 5;

	public function _construct()
	{
		$this->setFolder($this->_config()->getImportPath());
		return parent::_construct();
	}

	/**
	 * gets all
	 * cron:			sschollsolrcatalogannotationimport_solrimportAnnotation
	 * shedule-time:	* * * * *
	 */
	protected function _processFile($zip)
	{
		/* @var $zip SScholl_SolrCatalogAnnotationImporter_Model_Solrimport_Annotation_Zip */
		$zip = Mage::getModel('sschollsolrcatalogannotationimport/zip', $zip);
		if ($zip->extract()) {
			$folder = $zip->getFolder();
			$import = $folder->import();
			if ($import) {
				$this->_log(' mv to ' . $zip->getPathConverted(), $zip);
				$this->_logImported(" imported {$zip->getFolder()->getFiles()} files to solr", $zip);
				$zip->moveConverted();
			} else {
				$this->_setBreak();
				$this->_log(' NOT imported to solr', $zip);
				$zip->moveConvert();
			}
		}
		return true;
	}

	protected function _initFile($file)
	{
		return true;
	}

	private function _log($string, $zip)
	{
		Mage::log($zip->getFileName() . $string, null, self::LOG_FILE);
	}

	private function _logImported($string, $zip)
	{
		Mage::log($zip->getFileName() . $string, null, self::LOG_FILE_IMPORTED);
	}
	
	/**
	 * @var SScholl_SolrCatalogAnnotationImport_Helper_Data
	 */
	protected $_helper = null;
	
	/**
	 * @var SScholl_SolrCatalogAnnotationImport_Helper_Config
	 */
	protected $_configHelper = null;
	
	/**
	 * @return SScholl_SolrCatalogAnnotationImport_Helper_Data
	 */
	protected function _helper()
	{
		if (is_null($this->_helper))
			$this->_helper = Mage::helper('sschollsolrcatalogannotationimport');
		return $this->_helper;
	}
	
	/**
	 * @return SScholl_SolrCatalogAnnotationImport_Helper_Config
	 */
	protected function _config()
	{
		if (is_null($this->_configHelper))
			$this->_configHelper = Mage::helper('sschollsolrcatalogannotationimport/config');
		return $this->_configHelper;
	}

}