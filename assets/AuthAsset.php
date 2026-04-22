<?php

namespace app\assets;

use yii\web\AssetBundle;

class AuthAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
      "https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap",
"https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" ,
"css/site.css"

    ];

    public $js = [
        'https://cdn.tailwindcss.com?plugins=forms,container-queries',
        'js/tailwind.config.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
       // 'yii\bootstrap5\BootstrapAsset',
    ];
}