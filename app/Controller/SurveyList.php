<?php

namespace App\Controller;

use App\Model\FileDownloader;
use App\Model\LargeFileDownloader;
use App\Model\Propeller;


class SurveyList
{
    public function view()
    {
        if(isset($_SESSION['propellerToken'])){
    
            return [
                'template' => 'surveyList',
                // 'data'=> $_SESSION['surveyData'],
            ];
        }
        else{
            $_SESSION['flash_message'] = 'Accès non autorisé';
            $_SESSION['flash_alert'] = 'danger';
            return [
                'template' => 'homepage',
            ];
        }
    }
    
    public function downloadSurveys()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $jsonData = file_get_contents('php://input');
            $surveys = json_decode($jsonData, true); 
            if (!$surveys) {
                return [
                    'template' => 'error',
                    'error' => 'Missing required fields'
                ];
            }

            foreach ($surveys as $survey)
            {
                $propeller = new Propeller;
                // Get data by survey
                $organization_id = $survey['organization_id'];
                $site_id = $survey['site_id'];
                $survey_id = $survey['survey_id'];
                $site = $survey['site'];
                $name = $survey['name'];
                $date_captured = $survey['date_captured'];

                $date = strtotime($date_captured);
                $dateFormatted = date('Ymd', $date);

                // prepare the zipname
                $zipname = $site . '_' . $name . '_' . $dateFormatted;

                // get file's data and download files
                $filesData = $propeller -> getFilesList($organization_id, $site_id, $survey_id);
                $surveyDownloaded = $propeller -> downloadFiles($filesData);

                // Create zipfile
                $result = $propeller->createZip($surveyDownloaded, $zipname);
            }

            // $downloader = new LargeFileDownloader($data['urls']);
            // if ($downloader->downloadAndZip()) {
            //     header('Content-Type: application/zip');
            //     header('Content-Disposition: attachment; filename="propeller_files.zip"');
            //     header('Content-Length: ' . filesize('propeller_files.zip'));
            //     readfile('propeller_files.zip');
            //     unlink('propeller_files.zip');
            // } else {
            //     http_response_code(500);
            //     echo json_encode(['error' => "Error creating the ZIP"]);
            // }
            // exit;
            // return [
            //     'template' => 'homepage',
            // ];
        }
        
        // Si ce n'est pas un POST, rediriger vers la page d'accueil
        header('Location: /');
        exit;
    }
}

