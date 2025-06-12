<?php

use SGalinski\SgCookieOptin\Backend\TCAWarningField;
use SGalinski\SgCookieOptin\Command\DeleteUsageHistoryCommand;
use SGalinski\SgCookieOptin\Command\GenerateStaticFilesCommand;
use SGalinski\SgCookieOptin\Domain\Repository\SchedulerTaskRepository;
use SGalinski\SgCookieOptin\EventListeners\AfterBackendPageRenderEventListener;
use SGalinski\SgCookieOptin\Service\TaskSerializerService;
use SGalinski\SgCookieOptin\Wizards\TemplatePreviewLinkWizard;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
	$services->set(AfterBackendPageRenderEventListener::class)
		->tag('event.listener', ['event' => AfterBackendPageRenderEvent::class]);

	// Conditionally register SchedulerTaskRepository and TaskSerializerService
	if (
		class_exists(\TYPO3\CMS\Scheduler\Task\TaskSerializer::class)
		|| class_exists(\TYPO3\CMS\Scheduler\Serializer\TaskSerializer::class)
	) {
		$services->set(TaskSerializerService::class)
			->autowire()
			->autoconfigure();
		$services->set(SchedulerTaskRepository::class)
			->args([
				service(TaskSerializerService::class),
				service(\TYPO3\CMS\Core\Database\ConnectionPool::class),
			]);
	}
};
