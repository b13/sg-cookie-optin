<?php

namespace SGalinski\SgCookieOptin\Backend;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Core\Http\Response;

/**
 * Class Ajax
 *
 * @package SGalinski\SgAccount\Backend
 */
class Ajax {
	/**
	 * Checks whether the license is valid
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function checkLicense(
		ServerRequestInterface $request,
		ResponseInterface $response = NULL
	) {
		if ($response === NULL) {
			$response = new Response();
		}

		LicenceCheckService::setLastAjaxNotificationCheckTimestamp();
		$responseData = LicenceCheckService::getLicenseCheckResponseData(TRUE);
		$response->getBody()->write(json_encode($responseData));
		return $response;
	}
}
