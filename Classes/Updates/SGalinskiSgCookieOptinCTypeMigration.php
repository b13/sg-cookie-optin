<?php

declare(strict_types=1);

namespace SGalinski\SgCookieOptin\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('sgalinskiSgCookieOptinCTypeMigration')]
final class SGalinskiSgCookieOptinCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate "SGalinski SgCookieOptin" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "SGalinski SgCookieOptin" plugins are now registered as content element. Update migrates existing records and backend user permissions.';
    }

    /**
     * This must return an array containing the "list_type" to "CType" mapping
     *
     *  Example:
     *
     *  [
     *      'pi_plugin1' => 'pi_plugin1',
     *      'pi_plugin2' => 'new_content_element',
     *  ]
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            // TODO: Add this mapping yourself!
        ];
    }
}
