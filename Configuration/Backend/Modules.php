<?php

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'sg_cookie_optin_Optin' => [
        'parent' => 'web',
        'access' => 'user,group',
        'path' => '/module/sg_cookie_optin/optin',
        'iconIdentifier' => 'extension-sg_cookie_optin',
        'labels' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'SgCookieOptin',
        'controllerActions' => [
            \SGalinski\SgCookieOptin\Controller\OptinController::class => ['index', 'activateDemoMode', 'create', 'uploadJson', 'importJson', 'previewImport', 'exportJson'],
            \SGalinski\SgCookieOptin\Controller\StatisticsController::class => ['index'],
            \SGalinski\SgCookieOptin\Controller\ConsentController::class => ['index'],
        ],
    ],
];
