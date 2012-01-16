<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Ingo Pfennigstorf <pfennigstorf@sub.uni-goettingen.de>
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
 * Zentraler Controller fuer das Versenden von Benachrichtigungen
 * Funktioniert mit unterschiedlichen Methoden im Extbase Kontext und im Scheduler
 * @author Ingo Pfennigstorf <pfennigstorf@sub.uni-goettingen.de>
 */
class Tx_Schulungen_Controller_BenachrichtigungController extends Tx_Extbase_MVC_Controller_ActionController {

	protected $terminRepository;
	protected $teilnehmerRepository;
	/* 
	 * 0 = normale Erinnerung
	 * 1 = zu wenig Teilnehmer
	 * 2 = Termin wurde gecanceled
	 */
	private $mailType = array('Reminder', 'ReminderParticipation', 'ReminderCancelation');
	private $mailTypeMsg = array('Normale Erinnerung', 'Zu wenig Teilnehmer', 'Termin wurde abgesagt');

	/**
	 * Initializes the current action
	 *
	 * @return void
	 */
	protected function initializeAction() {
		$configurationManager = t3lib_div::makeInstance('Tx_Extbase_Configuration_ConfigurationManager');
		$extbaseFrameworkConfiguration = $configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$this->settings = $extbaseFrameworkConfiguration;

		$this->terminRepository = t3lib_div::makeInstance('Tx_Schulungen_Domain_Repository_TerminRepository');
		$this->teilnehmerRepository = t3lib_div::makeInstance('Tx_Schulungen_Domain_Repository_TeilnehmerRepository');
	}

	/**
	 * DI fuer Termine
	 *
	 * @param Tx_Schulungen_Domain_Repository_TerminRepository $terminRepository
	 */
	public function injectTerminRepository(Tx_Schulungen_Domain_Repository_TerminRepository $terminRepository) {
		$this->terminRepository = $terminRepository;
	}

	/**
	 * DI fuer Teilnehmer
	 *
	 * @param Tx_Schulungen_Domain_Repository_TeilnehmerRepository $teilnehmerRepository
	 */
	public function injectTeilnehmerRepository(Tx_Schulungen_Domain_Repository_TeilnehmerRepository $teilnehmerRepository) {
		$this->teilnehmerRepository = $teilnehmerRepository;
	}

	/**
	 * Action für den Scheduler: Berechnung der anstehenden Termine plus Mailversand an betroffene Teilnehmer
	 */
	public function sendeBenachrichtigungAction() {
		$this->initializeAction();
				
		$anstehendeTermine = $this->terminRepository->errechneAnstehendeTermine();
//		$this->view->assign('termine', $anstehendeTermine);
		
		foreach ($anstehendeTermine as $erinnerungsTermin) {
			$this->verschickeMailAnTeilnehmer($erinnerungsTermin->getTeilnehmer(), $erinnerungsTermin, true);
		}

		return TRUE;
	}

        /**
         * Action für das Backend: Einfacher Mailversand bei Ab/-Zusage von Schulungsterminen
         */
	public function sendeBenachrichtigungSofortAction($teilnehmer, $termin, &$obj) {
		$this->initializeAction();
		$result = $this->verschickeMailAnTeilnehmer($teilnehmer, $termin, true);
		return $result;
	}

	/**
	 * Logik-Methode: Legt Typ der zu versendenden Mails fest und sendet diese an übergebene Teilnehmer
	 * 
	 * Methode wird von Scheduler und Backend verwendet
	 * 
	 * Wegen Problemen mit der Darstellung der flashMessages (BE-Aufruf) 
	 * lassen sich diese über das $silent-Flag abschalten
	 */
	private function verschickeMailAnTeilnehmer($teilnehmer, $termin, $silent = false) {    
		$fail = false;
		
		$schulung = $termin->getSchulung();
		foreach ($teilnehmer as $person) {
			if(!$termin->isAbgesagt())  {
				if($termin->getAnzahlTeilnehmer() >= $schulung->getTeilnehmernMin())    {
						/* normale Erinnerung */
					$mailType = 0; $cc = false;
				}   else    {
						/* zu wenig Teilnehmer */
					$mailType = 1; $cc = false;
				}
			}   else    {
					/* Termin wurde ohne Begründung abgesagt */
				$mailType = 2; $cc = false;
			}  
			$result = $this->sendeMail($person, $mailType, $cc);

			if ($result) {
				if(!$silent) $this->flashMessageContainer->add('Eine E-Mail zur Erinnerung wurde an ' . $person->getEmail() . ' gesendet.');
				t3lib_div::devLog('Erinnerungsmail an ' . $person->getEmail() . ' gesendet.', $_EXTKEY, 0);
			} else {
				if(!$silent) $this->flashMessageContainer->add(Tx_Extbase_Utility_Localization::translate('tx_schulungen_email_versand.fail', 'schulungen'));
				t3lib_div::devLog('Mailversand an ' . $person->getEmail() . ' fehlgeschlagen!', $_EXTKEY, 0);
				$fail = true;
			}
		}

		if(!$fail)   {
			$termin->setErinnerungenVerschickt(true);
			$this->terminRepository->update($termin);

				/* Transaktionsmail an Admin/Redakteur */
			$mail = t3lib_div::makeInstance('Tx_Schulungen_Controller_EmailController');
			$result = $mail->sendeTransactionMail($this->settings['mail']['fromMail'], $this->settings['mail']['fromName'], 'Transaktionsinformation',
						array("teilnehmer" => $teilnehmer,
							  "action" => $this->mailTypeMsg[$mailType],
							  "schulung" => $schulung->getTitel(),
							  "termin" => $termin->getStartzeit()
							)
							/* ggf. noch Transaktion ergänzen */
					  );
		}

		return !$fail; // returns true, when all messages could be sent
	}

	private function sendeMail(Tx_Schulungen_Domain_Model_Teilnehmer $tn, $type, $cc) {

		$termin = $tn->getTermin();
		$schulung = $termin->getSchulung();
		$mail = t3lib_div::makeInstance('Tx_Schulungen_Controller_EmailController');
                
		$result = $mail->sendeMail($tn->getEmail(), $this->settings['mail']['fromMail'], $this->settings['mail']['fromName'], 'Erinnerung an Ihre Schulung', $this->mailType[$type],
					array("vorname" => $tn->getVorname(), 
						"nachname" => $tn->getNachname(), 
						"start" => $termin->getStartzeit(), 
						"ende" => $termin->getEnde(), 
						"schulung" => $schulung->getTitel(), 
						"mailcopy" => $schulung->getMailKopie(),
						"copy" => $cc
					)
				  );

		return $result;
	}

}

?>
