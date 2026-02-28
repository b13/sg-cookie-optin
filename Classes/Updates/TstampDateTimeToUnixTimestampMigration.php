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

namespace SGalinski\SgCookieOptin\Updates;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallIntType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('sgCookieOptinTstampDateTimeToUnixTimestampMigration')]
class TstampDateTimeToUnixTimestampMigration implements UpgradeWizardInterface {
	protected const TABLE_CONFIG = [
		'tx_sgcookieoptin_domain_model_user_preference' => [],
		'tx_sgcookieoptin_domain_model_rate_limit' => [
			'name' => 'rate_limit_check',
			'columns' => ['ip_hash', 'root_page_id', 'tstamp'],
		],
	];

	protected ConnectionPool $connectionPool;

	public function __construct(ConnectionPool $connectionPool) {
		$this->connectionPool = $connectionPool;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return 'Migrate sg_cookie_optin tstamp fields from DATETIME to Unix timestamps';
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): string {
		return 'Converts tstamp fields in usage history and rate limit tables to TYPO3 standard Unix timestamps.';
	}

	/**
	 * @inheritDoc
	 */
	public function executeUpdate(): bool {
		foreach (self::TABLE_CONFIG as $table => $indexConfiguration) {
			if (!$this->needsTableMigration($table)) {
				continue;
			}

			$this->migrateTable($table, $indexConfiguration);
		}

		return TRUE;
	}

	/**
	 * @inheritDoc
	 */
	public function updateNecessary(): bool {
		foreach (array_keys(self::TABLE_CONFIG) as $table) {
			if ($this->needsTableMigration($table)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @inheritDoc
	 */
	public function getPrerequisites(): array {
		return [DatabaseUpdatedPrerequisite::class, ];
	}

	/**
	 * Checks if a table still has a non-integer tstamp field.
	 *
	 * @param string $table
	 * @return bool
	 */
	protected function needsTableMigration(string $table): bool {
		$connection = $this->connectionPool->getConnectionForTable($table);
		$schemaManager = $connection->createSchemaManager();
		if (!$schemaManager->tablesExist([$table])) {
			return FALSE;
		}

		$columns = $schemaManager->listTableColumns($table);
		if (!isset($columns['tstamp'])) {
			return FALSE;
		}

		return !$this->isIntegerColumn($columns['tstamp']);
	}

	/**
	 * Executes the DATETIME -> Unix timestamp migration for a table.
	 *
	 * @param string $table
	 * @param array $indexConfiguration
	 * @return void
	 */
	protected function migrateTable(string $table, array $indexConfiguration): void {
		$connection = $this->connectionPool->getConnectionForTable($table);
		$queryBuilder = $connection->createQueryBuilder();
		$schemaManager = $connection->createSchemaManager();
		$columns = $schemaManager->listTableColumns($table);

		$tableIdentifier = $queryBuilder->quoteIdentifier($table);
		$tstampIdentifier = $queryBuilder->quoteIdentifier('tstamp');
		$tempTstampIdentifier = $queryBuilder->quoteIdentifier('tstamp_tmp');

		if (!isset($columns['tstamp_tmp'])) {
			$connection->executeStatement(
				'ALTER TABLE ' . $tableIdentifier . ' '
				. 'ADD COLUMN ' . $tempTstampIdentifier . " INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER $tstampIdentifier"
			);
		}

		$connection->executeStatement(
			'UPDATE ' . $tableIdentifier . ' '
			. 'SET ' . $tempTstampIdentifier . ' = '
			. 'COALESCE(UNIX_TIMESTAMP(NULLIF(' . $tstampIdentifier . ", '0000-00-00 00:00:00')), 0)"
		);

		if ($indexConfiguration !== [] && $this->hasIndex($table, (string) $indexConfiguration['name'])) {
			$connection->executeStatement(
				'ALTER TABLE ' . $tableIdentifier . ' '
				. 'DROP INDEX ' . $queryBuilder->quoteIdentifier((string) $indexConfiguration['name'])
			);
		}

		$connection->executeStatement('ALTER TABLE ' . $tableIdentifier . ' ' . 'DROP COLUMN ' . $tstampIdentifier);

		$connection->executeStatement(
			'ALTER TABLE ' . $tableIdentifier . ' '
			. 'CHANGE COLUMN ' . $tempTstampIdentifier . ' ' . $tstampIdentifier . ' '
			. 'INT(11) UNSIGNED NOT NULL DEFAULT 0'
		);

		if ($indexConfiguration !== [] && !$this->hasIndex($table, (string) $indexConfiguration['name'])) {
			$quotedColumns = array_map(
				static fn (string $column): string => $queryBuilder->quoteIdentifier($column),
				$indexConfiguration['columns']
			);

			$connection->executeStatement(
				'ALTER TABLE ' . $tableIdentifier . ' '
				. 'ADD INDEX ' . $queryBuilder->quoteIdentifier((string) $indexConfiguration['name']) . ' ('
				. implode(', ', $quotedColumns) . ')'
			);
		}
	}

	/**
	 * Checks if a table has a specific index.
	 *
	 * @param string $table
	 * @param string $indexName
	 * @return bool
	 */
	protected function hasIndex(string $table, string $indexName): bool {
		$connection = $this->connectionPool->getConnectionForTable($table);
		$indexes = $connection->createSchemaManager()->listTableIndexes($table);
		foreach (array_keys($indexes) as $existingIndexName) {
			if (strtolower((string) $existingIndexName) === strtolower($indexName)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Checks if a database column already is an integer type.
	 *
	 * @param Column $column
	 * @return bool
	 */
	protected function isIntegerColumn(Column $column): bool {
		$type = $column->getType();
		return $type instanceof IntegerType || $type instanceof BigIntType || $type instanceof SmallIntType;
	}
}
