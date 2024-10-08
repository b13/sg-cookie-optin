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

namespace SGalinski\SgCookieOptin\Endpoints;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SGalinski\SgCookieOptin\Exception\SaveOptinHistoryException;
use SGalinski\SgCookieOptin\Service\OptinHistoryService;
use TYPO3\CMS\Core\Http\Response;

/**
 * Class OptinHistoryController
 *
 * @package SGalinski\SgCookieOptin\Endpoints
 */
class OptinHistoryController {
	/**
	 * Save the user's preferences for the statistics
	 *
	 * @param ServerRequestInterface $request
	 * @param Response $response
	 * @return ResponseInterface
	 */
	public function saveOptinHistory(ServerRequestInterface $request, Response $response) {
		if (!isset($request->getParsedBody()['lastPreferences'])) {
			throw new SaveOptinHistoryException('No data passed');
		}

		$responseData = OptinHistoryService::saveOptinHistory(
			$request->getParsedBody()['lastPreferences'],
			$request->getAttribute('site')->getRootPageId()
		);

		$response->getBody()->write(json_encode($responseData));
		return $response
			->withStatus(200)
			->withHeader('Content-Type', 'application/json');
	}
}
