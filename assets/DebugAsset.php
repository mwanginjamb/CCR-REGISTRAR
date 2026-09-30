<?php

namespace app\assets;

use yii\web\AssetBundle;

class DebugAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/debug.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
}