<?php

return [
	'sg_cookie_optin:generate_static_files' => [
		'class' => \SGalinski\SgCookieOptin\Command\GenerateStaticFilesCommand::class,
	],
	'sg_cookie_optin:delete_usage_history' => [
		'class' => \SGalinski\SgCookieOptin\Command\DeleteUsageHistoryCommand::class,
	],
];
