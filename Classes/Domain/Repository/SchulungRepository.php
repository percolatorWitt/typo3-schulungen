<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Ingo Pfennigstorf <pfennigstorf@sub-goettingen.de>, Goettingen State Library
*  	
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Repository for Tx_Schulungen_Domain_Model_Schulung
 *
 * @version $Id: SchulungRepository.php 1974 2012-11-15 09:27:31Z simm $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

 class Tx_Schulungen_Domain_Repository_SchulungRepository extends Tx_Extbase_Persistence_Repository {

	protected $defaultPid = 1648;

	 // Sortierung absteigend nach Terminbeginn
	protected $defaultOrderings = array(
//			'titel' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
			'sort_index' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
//				'schulungTermine' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
	);

	public function findTranslated() {
		$query = $this->createQuery();
		$query->statement('SELECT * FROM `tx_schulungen_domain_model_schulung` WHERE `sys_language_uid` = 1 AND `deleted` = 0 AND `pid` = '. $this->defaultPid);
/*		$query->matching(
			$query->equals('sys_language_uid', 1)
		);
*/		$query->setOrderings(array('titel' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING));
		return $query->execute();
	}

		
}
?>