<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once '../abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Compiler extends Mage_Shell_Abstract
{

    /**
     * Run script
     *
     */
    public function run()
    {
    	$job = Mage::getSingleton('sschollsolrcatalogannotationimport/cronjob_import');
    	$job->run();
    	return;
    	
    	
//     	$fileName = "LibriTest.xml";
//     	$path = "/var/customers/webs/simon/magento/www/media/sschollsolrcatalogannotationimport/import/LibriTest.xml";
// 		$pathLocked = $path;
// 		$pathError = $path;
// 		$pathDone = $path;
// 		/* @var $onix SScholl_Onix_Model_File */
// 		$onix = Mage::getModel('sschollonix/file');
// 		$onix->init($fileName, $path, $pathLocked, $pathError, $pathDone);
// 		if ($onix->lock()) {
// 			if ( ($books = $onix->getBooks()) ) {
// 				/* @var $onix SScholl_SolrCatalogAnnotationImport_Model_Import */
// 				$import = Mage::getModel('sschollsolrcatalogannotationimport/import');
// 				$import->import($books);
// 				$onix->done();
// 			} else {
// 				$onix->error();
// 			}
// 		}
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f importCategories.php
USAGE;
    }
}

$shell = new Mage_Shell_Compiler();
$shell->run();
