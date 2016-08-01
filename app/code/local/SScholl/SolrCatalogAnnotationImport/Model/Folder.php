<?php /**************** Copyright notice ************************
 *  (c) 2011 Simon Eric Scholl <simon@sdscholl.de>
 *  All rights reserved
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 ***************************************************************/

class SScholl_SolrCatalogAnnotationImport_Model_Folder
	extends SScholl_SolrCatalogAnnotationImport_Model_Abstract
{
	
	/**
	 * @var SScholl_SolrCatalogAnnotationImport_Model_Solr
	 */
	private $_solr	= null;
	private $_files	= 0;
	
	public function __construct($path)
	{
		parent::__construct();
		//Mage::app()->setErrorHandler('solrimportMageCoreErrorHandler');
		$this->setPath($path);
		return $this;
	}
	
	public function import()
	{
		Mage::getResourceSingleton('sschollsolrcatalog/product')->setCollectionSave(true);
		$count = 0;
		$this->_helper()->setIsoEncoding($this->getPath());
		$annotations = array();
		$ids = array();
		foreach ( scandir($this->getPath()) as $annoFile ) {
			if ( !$this->_initAnno($annoFile) ) continue;
			//Mage::log($this->getPath() . ' convert to solr ' . $annoFile, null, self::LOG_FILE);
			/* @var $annotation SScholl_SolrCatalogAnnotationImport_Model_File */
			$annotation = Mage::getModel('sschollsolrcatalogannotationimport/file');
			if ( $annotation->init($annoFile, $this->getAnnoPath()) ) {
				$annotations[] = $annotation;
				$ids[] = $annotation->getId();
				if ( sizeof($annotations) > 25 ) {
					$this->_importAnnotations($annotations, $ids);
					$annotations = array();
					$ids = array();
				}
			}
		}
		if ( sizeof($annotations) > 0 ) {
			$this->_importAnnotations($annotations, $ids);
		}
		Mage::getResourceSingleton('sschollsolrcatalog/product')->saveCollection();
		if ( false ) {
			return false;
		}
		$this->_files += $count;
		return true;
	}
	
	protected function _importAnnotations($annotations, $ids)
	{
		$products = Mage::getResourceModel('sschollsolrcatalog/product_collection');
		$products->addAllFieldsToSelect();
		$products->addAttributeToFilter('entity_id', $ids);
		$products->setPage(1, 1000);
		$products->load();
		$products = $products->getItems();
		
		foreach ( $annotations as $annotation ) {
			if ( !isset($products[$annotation->getId()]) ) continue;
			$product = $products[$annotation->getId()];
			//$product = Mage::getModel('sschollsolrcatalog/product')->load($annotation->getId());
			if ( $annotation->getType() == 'KTEXT' ) $product->setDescription($annotation->getText());
			if ( !$product->getId() ) {
				continue;
			}
			try {
				$product->save();
				++ $count;
			} catch (Exception $e) {
				Zend_debug::dump($product);
				Zend_debug::dump($e->getMessage());
				Zend_debug::dump($e->getTraceAsString());
				Mage::logException($e);
				return false;
			}
		}
	}
	
	public function getFiles()
	{
		return $this->_files;
	}

	private function _initAnno($annoFile) {
		$this->setAnnoPath($this->getPath() . DS . $annoFile);
		if (
			$annoFile === '.'
			|| $annoFile === '..'
			|| (!stristr($annoFile, '.txt') && !stristr($annoFile, '.jpg'))
			|| !file_exists($this->getAnnoPath())
		) return false;
		return true;
	}
	
	/**
	 * @return SScholl_SolrCatalogAnnotationImport_Model_Solr
	 */
	protected function _solr()
	{
		if (is_null($this->_solr)) {
			$this->_solr = Mage::getModel('sschollsolrcatalogannotationimport/solr');
		}
		return $this->_solr;
	}
	
}