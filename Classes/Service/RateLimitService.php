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

namespace SGalinski\SgCookieOptin\Service;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use SGalinski\SgCookieOptin\Exception\RateLimitExceededException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RateLimitService
 */
class RateLimitService {
	public const TABLE_NAME = 'tx_sgcookieoptin_domain_model_rate_limit';
	public const MAX_REQUESTS_PER_HOUR = 10;

	/**
	 * Checks if the given IP address has exceeded the rate limit for the given root page.
	 * If not, records the request. If exceeded, throws an exception.
	 *
	 * @param string $ipAddress The IP address to check
	 * @param int $rootPageId The root page ID for scoping
	 * @return void
	 * @throws RateLimitExceededException
	 * @throws Exception
	 */
	public static function checkAndRecordRateLimit(string $ipAddress, int $rootPageId): void {
		$ipHash = self::anonymizeIpAddress($ipAddress);
		$currentTime = date('Y-m-d H:i:s');
		$oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
			?->getQueryBuilderForTable(self::TABLE_NAME);

		// Count requests from this IP in the last hour
		$requestCount = $queryBuilder
			->count('uid')
			->from(self::TABLE_NAME)
			->where(
				$queryBuilder->expr()->eq('ip_hash', $queryBuilder->createNamedParameter($ipHash)),
				$queryBuilder->expr()->eq(
					'root_page_id', $queryBuilder->createNamedParameter($rootPageId, ParameterType::INTEGER)
				),
				$queryBuilder->expr()->gte('tstamp', $queryBuilder->createNamedParameter($oneHourAgo))
			)
			->executeQuery()
			->fetchOne();

		if ($requestCount >= self::MAX_REQUESTS_PER_HOUR) {
			throw new RateLimitExceededException(
				'Rate limit exceeded: maximum ' . self::MAX_REQUESTS_PER_HOUR . ' requests per hour allowed'
			);
		}

		// Record this request
		$queryBuilder
			->insert(self::TABLE_NAME)
			->values([
				'ip_hash' => $ipHash,
				'tstamp' => $currentTime,
				'root_page_id' => $rootPageId,
				'pid' => 0, // Root level
			])
			->executeStatement();
	}

	/**
	 * Anonymizes an IP address by hashing it.
	 * This provides privacy while still allowing rate limiting by IP.
	 *
	 * @param string $ipAddress The IP address to anonymize
	 * @return string The hashed IP address
	 */
	public static function anonymizeIpAddress(string $ipAddress): string {
		// Use SHA-256 hash with a salt for additional security
		$salt = 'sg_cookie_optin_rate_limit_salt';
		return hash('sha256', $ipAddress . $salt);
	}

	/**
	 * Gets the client's IP address from the current request.
	 *
	 * @return string The client's IP address
	 */
	public static function getClientIpAddress(): string {
		$serverParams = $GLOBALS['TYPO3_REQUEST']->getServerParams();

		// Check for forwarded IP addresses first (common with proxies/load balancers)
		$forwardedFor = $serverParams['HTTP_X_FORWARDED_FOR'] ?? '';
		if (!empty($forwardedFor)) {
			// Take the first IP in case of multiple forwarded IPs
			$ips = explode(',', $forwardedFor);
			$ip = trim($ips[0]);
		} else {
			$ip = $serverParams['REMOTE_ADDR'] ?? '';
		}

		// Validate IP address
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			$ip = '127.0.0.1'; // Fallback to localhost
		}

		return $ip;
	}

	/**
	 * Cleans up old rate limit entries older than the specified hours.
	 *
	 * @param int $olderThanHours
	 * @return void
	 * @throws Exception
	 */
	public static function cleanupOldEntries(int $olderThanHours = 24): void {
		$connection = GeneralUtility::makeInstance(ConnectionPool::class)
			?->getConnectionForTable(self::TABLE_NAME);

		$query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE tstamp < DATE_SUB(NOW(), INTERVAL ? HOUR)';
		$connection->executeQuery($query, [$olderThanHours]);
	}
}
