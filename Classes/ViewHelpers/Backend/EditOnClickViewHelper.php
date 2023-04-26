<?php

namespace SGalinski\SgCookieOptin\ViewHelpers\Backend;

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

use SGalinski\SgCookieOptin\ViewHelpers\Backend\Traits\EditOnClickViewHelperTrait;

if (class_exists('\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper')) {
    class EditOnClickViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
    {
        use EditOnClickViewHelperTrait;
    }
} else {
    class EditOnClickViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
    {
        use EditOnClickViewHelperTrait;
    }
}