# Breaking changes in Version 7

- Dropped support for TYPO3 versions prior to 12LTS
- With this support drop, we also removed a bunch of legacy classes and JavaScript
- LegacyConsentController removed
- LegacyOptinController removed
- LegacyStatisticsController removed
- LegacyInitControllerComponents removed
- ViewHelpers/Legacy/Be/Menus/ActionMenuOptionGroupViewHelper removed
- ViewHelpers/Legacy/Backend/ControlViewHelper removed
- ViewHelpers/Legacy/Backend/EditOnClickViewHelper removed
- ViewHelpers/Legacy/Backend/IconViewHelper removed
- ViewHelpers/Legacy/Backend/IsVersionHigherThanViewHelper removed
- ViewHelpers/Legacy/Backend/ActionMenuItemViewHelper removed
- Resources/Public/JavaScript/Backend/Legacy/ConsentManagement.js removed
- Resources/Public/JavaScript/Backend/Legacy/EditOnClick.js removed
- Resources/Public/JavaScript/Backend/Legacy/LicenseNotification.js removed
- Resources/Public/JavaScript/Backend/Legacy/Statistics.js removed
- Moved the plugins from the `list`-type to their own `CTypes` (please execute the UpgradeWizard `sgalinskiSgCookieOptinCTypeMigration`) in the Install Tool upgrade section
