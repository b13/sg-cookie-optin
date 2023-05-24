<?php

namespace SGalinski\SgCookieOptin\ViewHelpers\V9\Backend;

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

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class EditLink
 **/
if (!class_exists('\SgCookieAbstractViewHelper')) {
    class IsVersionHigherThanViewHelper {

    }
} else {
    class IsVersionHigherThanViewHelper extends \SgCookieAbstractViewHelper
    {
        /**
         * Register the ViewHelper arguments
         */
        public function initializeArguments()
        {
            parent::initializeArguments();
            $this->registerArgument('version', 'string', 'The version number to compare with', TRUE);
        }

        /**
         * Checks if the O3 version meets the requirements
         *
         * @return string
         * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
         */
        public function render()
        {
            $version = $this->arguments['version'];
            return version_compare(VersionNumberUtility::getNumericTypo3Version(), $version, '>=');
        }
    }
}
