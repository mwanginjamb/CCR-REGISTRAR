<?php

return [
    'adminEmail' => env('SMTP_USERNAME'),
    'senderEmail' => env('SMTP_USERNAME'),
    'senderName' => 'KEMRI CCR CRF mailer',
    'supportEmail' => env('SMTP_USERNAME'),
    'meta_description' => 'OncoRegistry is a comprehensive cancer registry management system designed to streamline data collection, analysis, and reporting for oncology research and clinical care.',
    'meta_keywords' => 'cancer registry, oncology data management, clinical data collection, cancer research, patient data analysis, medical registry software, healthcare data management, oncology registry system',
    'APP_NAME' => env('APP_NAME', 'KEMRI ONCO Register'),
    'user.passwordMinLength' => 8,
    'user.passwordResetTokenExpire' => 3600,
    // Dropdown option label format, see app\library\OptionLabel
    'dropdown.labelTemplate' => '{key} - {label}',
];
