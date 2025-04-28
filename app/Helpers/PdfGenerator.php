<?php

namespace App\Helpers;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfGenerator
{
    public static function generatePdf($view, $data, $filename)
    {
        $dataArray = is_array($data) ? $data : ['data' => $data];

        $pdf = PDF::loadView($view, $dataArray);

        $directory = public_path('uploads/pdf');

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        $filePath = $directory . "/{$filename}.pdf";

        $pdf->save($filePath);

        return $filePath;
    }
}
