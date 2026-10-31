<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the guest layout (login, signup, password reset).
 *
 * Uses the compiled Tailwind build (npm run build -> web/css/tailwind.css),
 * which the service worker also caches, so auth pages render offline.
 * Fonts are linked directly in views/layouts/guest.php.
 */
class AuthAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/tailwind.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
