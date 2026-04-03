<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License or
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

namespace SGalinski\SgCookieOptin\Controller;

use Doctrine\DBAL\Exception;
use SGalinski\SgCookieOptin\Domain\Repository\SchedulerTaskRepository;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\ExecuteSchedulableCommandTask;

/**
 * Abstract controller to share session-based "mode switching" logic
 */
abstract class AbstractController extends ActionController {
	/**
	 * For storing the current "lastController" in the user session.
	 */
	public const SESSION_KEY = 'sg_cookie_optin_mode';

	/**
	 * @var SchedulerTaskRepository
	 */
	protected ?SchedulerTaskRepository $schedulerTaskRepository = NULL;

	/**
	 * Read from user session
	 */
	protected function getFromSession(string $key): ?string {
		// For safety, if there's no data, return empty string or null
		return $GLOBALS['BE_USER']->getSessionData(self::SESSION_KEY . '_' . $key);
	}

	/**
	 * Write to user session
	 */
	protected function writeToSession(string $key, $data): void {
		$GLOBALS['BE_USER']->setAndSaveSessionData(self::SESSION_KEY . '_' . $key, $data);
	}

	/**
	 * The heart of the "remember last submodule" logic.
	 *
	 *  - Checks `parameters[lastController]` in the incoming request (from the <f:be.menus.actionMenu> link).
	 *  - Stores it in the session.
	 *  - If the current controller class does NOT match the stored mode, do a redirect to the correct controller.
	 *
	 * @throws PropagateResponseException
	 */
	protected function switchMode(): void {
		// Attempt to get the current mode from session
		$mode = $this->getFromSession('mode');

		// Check for new "lastController" in the query params
		$queryParams = $this->request->getQueryParams();
		if (isset($queryParams['parameters']['lastController'])) {
			$mode = $queryParams['parameters']['lastController'];
		}

		if ($mode) {
			// Store (or update) the mode in the session
			$this->writeToSession('mode', $mode);

			// Build the fully qualified controller class we expect
			$expectedClass = 'SGalinski\\SgCookieOptin\\Controller\\' . $mode . 'Controller';

			// If we're not in the correct controller, redirect
			if ($expectedClass !== static::class) {
				// This calls $this->redirect('index', $mode) and then
				// *forces* an immediate redirect
				$redirectResponse = $this->redirect('index', $mode);
				throw new PropagateResponseException($redirectResponse);
			}
		}
	}

	/**
	 * Fetches available scheduler tasks and filters them (first by class, then by the table argument).
	 * We do this, to be able to show a warning flash message to the user, in case the task is not set up.
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function isGarbageCollectionTaskSetUpForCookieOptin(): bool {
		$taskExists = FALSE;
		// fetch all scheduler tasks
		$allTasks = $this->schedulerTaskRepository->fetchTasksWithCondition('', TRUE);
		/** @var AbstractTask $aTaskObject */
		foreach ($allTasks as $aTaskObject) {
			// skip tasks, that are not of class DeleteUsageHistoryCommand
			if (\get_class($aTaskObject) === ExecuteSchedulableCommandTask::class
				&& $aTaskObject->getCommandIdentifier() === 'sg_cookie_optin:delete_usage_history') {
				$taskExists = TRUE;
			}
		}

		return $taskExists;
	}
}
