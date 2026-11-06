<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * PWA and favicon tags shared by every layout (main, questionnaire, guest).
 * Render inside <head>, before $this->head().
 *
 * Registers the service worker on every page, so the app can be installed
 * from any page (including login), not only from the patient form.
 * patientForm.js also registers /sw.js; registering the same script twice
 * returns the same registration, so this is safe.
 *
 * @var yii\web\View $this
 */

$appName = 'OncoReg';
?>
<link rel="manifest" href="<?= Url::to('@web/manifest.json') ?>">
<meta name="theme-color" content="#001A48">
<meta name="application-name" content="<?= Html::encode($appName) ?>">

<?php /* Favicons: .ico for legacy/browser tabs, PNGs for high-DPI */ ?>
<link rel="icon" href="<?= Url::to('@web/favicon.ico') ?>" sizes="16x16 32x32 48x48">
<link rel="icon" type="image/png" sizes="32x32" href="<?= Url::to('@web/icons/icon-32x32.png') ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= Url::to('@web/icons/icon-192.png') ?>">

<?php /* iOS home-screen install */ ?>
<link rel="apple-touch-icon" href="<?= Url::to('@web/icons/icon-180x180.png') ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="<?= Html::encode($appName) ?>">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?= Url::to('@web/sw.js') ?>').catch(function (err) {
                console.warn('Service worker registration failed:', err);
            });
        });
    }
</script>
