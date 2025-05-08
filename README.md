# Ext: sg_cookie_optin

## Installation

1. Install this extension with the Extension Manager, or with composer.

2. Go to the extension configuration and set your license key and output folder. You must set this folder accordingly in
   case your TYPO3 installation is in a subdirectory relative to the web server document root.

3. Add the static TypoScript named "Cookie Consent" to your instance with the "Template" backend module.

    - Open up the "Template" module in the backend of TYPO3.
    - Go to your root site page within the page tree.
    - Choose "Info/Modify" at the select on the top.
    - Click on the button "Edit the whole template record."
    - Select the tab "Includes."
    - Choose the template "Cookie Consent (sg_cookie_optin)" on the multi select box with the name "Include static (from
      extensions)"
    - Save

4. Go into the "Cookie Consent" backend module, configure it and save it once.

## Update from version 6 to version 7

If you update from sg_cookie_optin v6, please follow the instructions in [UPGRADE.md](UPGRADE.md).

## How to add scripts / How to rewrite the script HTML?

Unfortunately, we can't support HTML code for the cookie scripts, because of security cases. So you need to rewrite the
HTML code to javascript. Here's an example for the Google Tag Manager:

HTML:

```html
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());
    gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

JavaScript:

```javascript
var script = document.createElement('script');
script.setAttribute('type', 'text/javascript');
script.setAttribute('async', true);
script.setAttribute('src', 'https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID');
document.body.appendChild(script);

window.dataLayer = window.dataLayer || [];

function gtag() {
    dataLayer.push(arguments);
}

gtag('js', new Date());
gtag('config', 'GA_MEASUREMENT_ID');
```

## How is the structure of our cookie?

In order for us to know which cookie groups the user has accepted, we must also store an essential cookie.
The structure is as follows:

**Name**: cookie_optin
**Example data**: essential:1|analytics:0|performance:1
**Explanation**: The user has accepted the essential and performance groups, but not the analytics one.

## Additional Features

### Open a page without showing the cookie consent

Just add the parameter "?disableOptIn=1" to your URL, so the necessary JavaScript and Css, which shows the dialog, isn't
loaded anymore. Here is an example:

```
https://www.sgalinski.de/?disableOptIn=1
```

### Show the cookie consent, after accepting it

Just add the parameter "?showOptIn=1" to your URL, so the dialog shows up again and the accepted cookies can be
modified.
Here is an example:

```
https://www.sgalinski.de/?showOptIn=1
```

### External content

#### Add an additional description for a iframe on the opt in

Just add the data attribute "data-consent-description" to an iframe HTML tag, like in the example below:

```html

<iframe width="560" height="315" src="https://www.youtube-nocookie.com/XYZ"
        data-consent-description="An additional description about this video!"></iframe>
```

#### Change the button text for a specific external content element

Add the data attribute "data-consent-button-text" to an iframe HTML tag, like in the example below:

```html

<iframe width="560" height="315" src="https://www.youtube-nocookie.com/XYZ"
        data-consent-button-text="Custom text here"></iframe>
```

#### Whitelist an element for the external content opt in logic

There are three ways to do this, and all of them will result in having this element and all of its children whitelisted
for the external content protection:

1. Just add the data attribute "data-iframe-allow-always" or "data-external-content-no-protection" to an iframe HTML
   tag, like in the example below:

```html

<iframe width="560" height="315" src="https://www.youtube-nocookie.com/XYZ" data-iframe-allow-always="1"></iframe>
```

2. Add the class "frame-external-content-no-protection" to the HTML tag.

```html

<iframe width="560" height="315" src="https://www.youtube-nocookie.com/XYZ"
        class="frame-external-content-no-protection"></iframe>
```

3. From the TYPO3 Backend Page module edit the element's appearance and set the Frame Class "Unprotected External
   Content"

#### Protect any kind of DOM element with the external content protection (force opt-in)

There are three ways to do this, and all of them will result in replacing this element and all of its contents with the
opt-in dialog:

1. Add the data attribute "data-external-content-protection" to the HTML tag.

```html

<div class="test-content-protection" data-external-content-protection="1">
    Content comes here
</div>
```

2. Add the class "frame-external-content-protection" to the HTML tag.

```html

<div class="test-content-protection frame-external-content-protection">
    Content comes here
</div>
```

3. From the TYPO3 Backend Page module edit the element's appearance and set the Frame Class "External Content"

### Modify the generated JSON file

You can do that by attaching to the
hook `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sg_cookie_optin']['GenerateFilesAfterTcaSave']['preSaveJsonProc']`.
It sends an array $params with the following entries:

- `pObj` = An instance of the StaticFileGenerationService. It contains the siteRootId as well as a few public methods
  that
  can come in handy.
- `data` = A reference to the data array that will be written in the JSON file.
- `languageUid` = The uid of the current language

Example:

```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sg_cookie_optin']['GenerateFilesAfterTcaSave']['preSaveJsonProc'][] =
    function ($params) {
        $params['data']['newDataEntry'] = 'newValue';
    };
```

## Extending the Statistics Module

The `Statistics.js` module in `sg-cookie-optin` is bundled using [Vite](https://vitejs.dev/) to ensure compatibility with TYPO3 v13. This module incorporates dependencies like Chart.js and provides a flexible way to extend or modify its behavior for custom use cases.

### Overview of the Bundling Configuration

The Vite configuration ensures that the module is output to:

```
Resources/Public/JavaScript/Backend/dist/statistics.es.js
```

This file is optimized for modern browsers (`es` format) and includes all necessary dependencies, such as Chart.js.

### Prerequisites for Extension

To extend or customize the `Statistics.js` module, ensure you have the following:

1. **Node.js** installed (version 16 or later recommended).
2. **Vite** installed in your local environment. You can install it globally or as a development dependency:
   ```bash
   npm install vite --save-dev
   ```

3. Familiarity with modern JavaScript and ES Modules.

### Extending the Module

Follow these steps to modify or extend the functionality:

1. **Clone the Repository**
   Clone the sg-cookie-optin extension repository or work on your local copy.

2. **Locate the Source File**
   The `Statistics.js` source file is located at:
   ```
   Resources/Public/JavaScript/Backend/Statistics.js
   ```

3. **Modify the Code**
   Open `Statistics.js` and implement your changes. For example, you might want to add a new chart type or extend existing functionality.

4. **Rebuild the Module**
   After modifying the code, rebuild the module using Vite:
   ```bash
   npm run build
   ```
   This will regenerate the bundled file in:
   ```
   Resources/Public/JavaScript/Backend/dist/statistics.es.js
   ```

5. **Integrate the Updated Script**
   Ensure the new `statistics.es.js` file is correctly loaded in your TYPO3 backend module. Clear caches in TYPO3 to reflect the changes.
