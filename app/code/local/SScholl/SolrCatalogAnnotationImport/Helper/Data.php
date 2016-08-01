<?php /**************** Copyright notice ************************
 *  (c) 2011 Simon Eric Scholl <simon@sdscholl.de>
 *  All rights reserved
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 ***************************************************************/

class SScholl_SolrCatalogAnnotationImport_Helper_Data extends Mage_Core_Helper_Abstract
{

	const ANNOTATION_CODEPAGE_CONVERT = '/usr/bin/codepagePara.sh ';

	public function setIsoEncoding($folder)
	{
		shell_exec(
			'sh '
			//. Mage::getBaseDir() . self::ANNOTATION_CODEPAGE_CONVERT
			. self::ANNOTATION_CODEPAGE_CONVERT
			. $folder
		);
	}

}