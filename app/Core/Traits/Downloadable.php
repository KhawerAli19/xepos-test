<?php

namespace App\Core\Traits;

use App\Models\Challenge;

trait Downloadable
{

    protected function wordDownload($data, $request)
    {

        $wordTest = new \PhpOffice\PhpWord\PhpWord();
        $newSection = $wordTest->addSection();

        $newSection->addText('Date' . ',' . 'Title' . ',' . 'Type');
        foreach ($data as $task) {
            $newSection->addText($task->created_at->format('d/m/y') . ',  ' . ($request->type == 'medicine' ? 'Medicine Reminder' : 'Appointment Reminder') . ',  ' . ($request->type == 'medicine' ? 'Medicine' : 'Appointment'));
        }

        $objectWriter = \PhpOffice\PhpWord\IOFactory::createWriter($wordTest, 'Word2007');
        try {
            $objectWriter->save(storage_path('Reports.docx'));
        } catch (\Exception $e) {
        }

        return response()->download(storage_path('Reports.docx'));
    }
    protected function excelDownload($data, $request)
    {

        $fileName = 'Reports.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Date', 'Title', 'Type');

        $callback = function () use ($data, $columns, $request) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $task) {
                $row['Date']  = $task->created_at->format('d/m/y');
                $row['Title']    = $request->type == 'medicine' ? 'Medicine Reminder' : 'Appointment Reminder';
                $row['Type']    =  $request->type == 'medicine' ? 'Medicine' : 'Appointment';

                fputcsv($file, array($row['Date'], $row['Title'], $row['Type']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
