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
 * Repository for Tx_Schulungen_Domain_Model_Teilnehmer
 *
 * @version $Id: TeilnehmerRepository.php 1887 2012-07-02 14:19:47Z simm $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class Tx_Schulungen_Domain_Repository_TeilnehmerRepository extends Tx_Extbase_Persistence_Repository {

	// Sortierung absteigend nach Terminbeginn
	protected $defaultOrderings = array(
			'nachname' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
	);

	public function teilnehmerAngemeldet(Tx_Schulungen_Domain_Model_Teilnehmer $teilnehmer, Tx_Schulungen_Domain_Model_Termin $termin) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd( 
				$query->equals('termin', $termin),
				$query->equals('email', $teilnehmer->getEmail()),
				$query->equals('vorname', $teilnehmer->getVorname()),
				$query->equals('nachname', $teilnehmer->getNachname())
			)
		);
		$query->setOrderings(array('nachname' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING));
		return $query->execute();
	}
}
?>