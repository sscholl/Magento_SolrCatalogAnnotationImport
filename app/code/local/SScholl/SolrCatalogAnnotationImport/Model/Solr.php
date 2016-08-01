<?php /**************** Copyright notice ************************
 *  (c) 2011 Simon Eric Scholl <simon@sdscholl.de>
 *  All rights reserved
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 ***************************************************************/

class SScholl_SolrCatalogAnnotationImport_Model_Solr
	extends SScholl_Solr_Model_Solr
{

	const LOG_FILE			= 'sschollsolrcatalogannotationimport_solrimport_anno_solr.log';
	const LOG_FILE_ERROR	= 'sschollsolrcatalogannotationimport_solrimport_anno_solr_error.log';

	const VALID_RESPONSE	= '200';
	
	/**
	 * @var Solarium_Query_Update
	 */
	private $_update	= null;
	
	private $_time		= null;
	
	public function __construct()
	{
		$number = 1; //Mage::helper('sschollsolrcatalogannotationimport/config_annotation')->solrNumber();
		parent::__construct($number);
	}
	
	/**
	 * @return Solarium_Query_Update
	 */
	public function getUpdate()
	{
		if (is_null($this->_update)) {
			$this->_update = $this->getSolarium()->createUpdate(); 
		}
		return $this->_update;
	}
	
	/**
	 * adds the annotation to the solr
	 * @param SScholl_SolrCatalogAnnotationImport_Model_File $annotation
	 */
	public function add($annotation)
	{
		$product = Mage::getModel('sschollsolrcatalog/product')->load($annotation->getId());
		if ( $annotation->getType() == 'KTEXT' ) $product->setDescription($annotation->getText());
		try {
			$product->save();
		} catch (Exception $e) {
			Zend_debug::dump($product);
			Zend_debug::dump($e->getMessage());
			Zend_debug::dump($e->getTraceAsString());
			Mage::logException($e);
			break;
		}
		return;
		
		
		
		$annotation->getId();//@TODO HERE!!!
		if ($annotation->isImage()) $annotation->getImagePath();
		if ($annotation->isText()) $annotation->getText();
		
		
		$doc = $this->getUpdate()->createDocument();
		if (!($doc->Libri_Filename || $doc->Libri_Text)) {
			Mage::log($annotation->getFile() . ' defect add', null, self::LOG_FILE_ERROR);
			return false;
		}
		$doc->id							= $annotation->getIdType() . '_' . $annotation->getId() . '_' . $annotation->getType();
		$doc->Libri_Product_Identifier_Type	= $annotation->getIdType();
		$doc->Libri_Product_Identifier		= $annotation->getId();
		$doc->Libri_Annotation_Type			= $annotation->getType();
		$doc->Libri_Insert_Time				= $this->_getTime();
		$this->getUpdate()->addDocument($doc);
Zend_Debug::dump($doc);exit;
		return true;
	}
	
	public function update($file) 
	{
		Mage::log($file . ' begin update for adds', null, self::LOG_FILE);
		try {
			$this->getUpdate()->addCommit();
			$response = $this->getSolarium()->update($this->getUpdate());
			$responseCode = $response->getResponse()->getStatusCode();
			if ($responseCode === '200') {
				//Mage::log($file . " end  update - response: $responseCode", null, self::LOG_FILE);
				$this->_update = null;
			} else {
				Mage::log($file . ' connection error: ' . $responseCode . ' Response: ' . serialize($response), null, self::LOG_FILE_ERROR);
			}
		} catch (Exception $e) {
			Mage::log($file . ' connection error: ' . $e->getMessage(), null, self::LOG_FILE_ERROR);
			$responseCode = '0';
		}
		return $responseCode;
	}
	
	private function _getTime()
	{
		if (is_null($this->_time))
			$this->_time = date('Y-m-d') . 'T' . date('H:i:s') . 'Z';
		return $this->_time;
	}

}