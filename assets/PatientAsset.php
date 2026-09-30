<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class PatientAsset extends AssetBundle
{
    public $sourcePath = null; // all resources are external CDN

    public $css = [
        'https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap',
        'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap',
        'https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css',
        'https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.min.css',
    ];

    public $js = [
        // JSZip + pdfmake must load before Buttons
        'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js',
        'https://cdn.datatables.net/2.0.8/js/dataTables.min.js',
        'https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js',
        'https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js',
        'https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js',
    ];

    public $jsOptions = ['position' => View::POS_END];

    public $depends = [
        'app\assets\AppAsset',
        \yii\web\JqueryAsset::class, // DataTables requires jQuery
    ];
}