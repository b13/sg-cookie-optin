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

namespace SGalinski\SgCookieOptin\Domain\Repository;

use Doctrine\DBAL\Exception;
use SGalinski\SgCookieOptin\Service\TaskSerializerService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Scheduler\Exception\InvalidTaskException;
use TYPO3\CMS\Scheduler\Validation\Validator\TaskValidator;

class SchedulerTaskRepository {
	protected TaskSerializerService $taskSerializerService;

	protected ConnectionPool $connectionPool;

	public function __construct(
		TaskSerializerService $taskSerializerService,
		ConnectionPool $connectionPool
	) {
		$this->taskSerializerService = $taskSerializerService;
		$this->connectionPool = $connectionPool;
	}

	/**
	 * Fetches and unserializes task objects selected with some (SQL) condition
	 * Objects are returned as an array
	 *
	 * @param string $where Part of a SQL where clause (without the "WHERE" keyword)
	 * @param bool $includeDisabledTasks TRUE if disabled tasks should be fetched too, FALSE otherwise
	 * @return array List of task objects
	 * @throws Exception
	 */
	public function fetchTasksWithCondition(string $where, bool $includeDisabledTasks = FALSE): array {
		$tasks = [];
		$queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_scheduler_task');

		$queryBuilder
			->select('serialized_task_object')
			->from('tx_scheduler_task')
			->where(
				$queryBuilder->expr()->eq(
					'deleted',
					$queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
				)
			);

		if (!$includeDisabledTasks) {
			$queryBuilder->andWhere(
				$queryBuilder->expr()->eq(
					'disable',
					$queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
				)
			);
		}

		if (!empty($where)) {
			$queryBuilder->andWhere(self::stripLogicalOperatorPrefix($where));
		}

		$result = $queryBuilder->executeQuery();
		while ($row = $result->fetchAssociative()) {
			try {
				$task = $this->taskSerializerService->deserialize($row['serialized_task_object']);
			} catch (InvalidTaskException) {
				continue;
			}

			// Add the task to the list only if it is valid
			if ((new TaskValidator())->isValid($task)) {
				$task->setScheduler();
				$tasks[] = $task;
			}
		}

		return $tasks;
	}

	/**
	 * Removes the prefixes AND/OR from the input string.
	 *
	 * This function should be used when you can't guarantee that the string
	 * that you want to use as a WHERE fragment is not prefixed.
	 *
	 * @param string $constraint The where part fragment with a possible leading AND or OR operator
	 * @return string The modified where part without leading operator
	 */
	public static function stripLogicalOperatorPrefix(string $constraint): string {
		return preg_replace('/^(?:(AND|OR)[[:space:]]*)+/i', '', trim($constraint)) ?: '';
	}
}
