<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) sgalinski Internet Services (https://www.sgalinski.de)
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
 ***************************************************************/

use SGalinski\SgCookieOptin\Backend\TCAWarningField;
use SGalinski\SgCookieOptin\Command\DeleteUsageHistoryCommand;
use SGalinski\SgCookieOptin\Command\GenerateStaticFilesCommand;
use SGalinski\SgCookieOptin\Wizards\TemplatePreviewLinkWizard;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

return static function (ContainerConfigurator $containerConfigurator): void {
	$services = $containerConfigurator->services();
	$services->defaults()
		->private()
		->autowire()
		->autoconfigure();
	$services->load('SGalinski\\SgCookieOptin\\', __DIR__ . '/../Classes/');
	$services->set(GenerateStaticFilesCommand::class)
		->tag('console.command', [
			'command' => 'sg_cookie_optin:generate_static_files',
			'description' => 'Generates the necessary JavaScript, JSON and CSS files.'
		]);
	$services->set(DeleteUsageHistoryCommand::class)
		->tag('console.command', [
			'command' => 'sg_cookie_optin:delete_usage_history',
			'description' => 'Deletes the optin usage history entries older than X days'
		]);
	$services->set('SGalinski\\SgCookieOptin\\ViewHelpers')
		->public();
	$services->set(AbstractViewHelper::class)
		->public();
	$services->set(TCAWarningField::class)
		->autowire(FALSE);
	$services->set(TemplatePreviewLinkWizard::class)
		->autowire(FALSE);
};
