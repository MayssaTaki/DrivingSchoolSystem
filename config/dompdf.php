<?php

return [

    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => true,

    'options' => [
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),

        'allowed_protocols' => [
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
            'data://' => ['rules' => []],
        ],

        'log_output_file' => null,
        'enable_font_subsetting' => true,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',

        // 👇 مهم: تحديد الخط الافتراضي الداعم للعربية
        'default_font' => 'Amiri',
        'font_family' => [
    'Amiri' => [
        'R' => 'Amiri-Regular.ttf',
    ],],

        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => true,

        // 👇 دعم HTML5 أفضل
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled' => false,
        'isRemoteEnabled' => true,
        'isJavascriptEnabled' => true,
        'isFontSubsettingEnabled' => true,

        'debugPng' => false,
        'debugKeepTemp' => false,
        'debugCss' => false,
        'debugLayout' => false,
        'debugLayoutLines' => false,
        'debugLayoutBlocks' => false,
        'debugLayoutInline' => false,
        'debugLayoutPaddingBox' => false,

      
    ],
];
