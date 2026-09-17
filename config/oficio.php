<?php

return [

    /*
    | LibreOffice (soffice) para convertir el oficio DOCX a PDF en la vista previa.
    | En Windows suele estar en Program Files; en Linux, "soffice" en PATH.
    */
    'libreoffice_path' => env('OFICIO_LIBREOFFICE_PATH', PHP_OS_FAMILY === 'Windows'
        ? 'C:\\Program Files\\LibreOffice\\program\\soffice.exe'
        : 'soffice'),

    'pdf_cache_minutes' => (int) env('OFICIO_PDF_CACHE_MINUTES', 120),

];
