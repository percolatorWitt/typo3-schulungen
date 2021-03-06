<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Ingo Pfennigstorf <pfennigstorf@sub.uni-goettingen.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * ************************************************************* */

/**
 * Klasse zur Berechnung der nächsten Termine
 *
 * @author dsimm
 */
class Tx_Schulungen_ViewHelpers_EndSoonViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Berechnet, ob der übergebene Termin in den nächsten 2 Tage stattfindet
	 *
	 * @param DateTime $time Startzeitpunkt des Termin
	 * @return $result Ended bald: true/false
	 */
	public function render($time) {

		$currentTime = new DateTime();
		$currentTime->setTimestamp(time());

		$interval = date_diff($currentTime, $time);

		if ($currentTime < $time && $interval->format("%r%a") <= 2) {
			return $result = true;
		} else {
			return $result = false;
		}
	}

}

?>
