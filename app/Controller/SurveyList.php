<?php

namespace App\Controller;

use App\Model\FileDownloader;
use App\Model\LargeFileDownloader;


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
    
    public function download()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            var_dump($data['urls']);
            if (!$data) {
                return [
                    'template' => 'error',
                    'error' => 'Missing required fields'
                ];
            }

            $_POST['action'] = $data['action'];
            $_POST['urls'] = $data['urls'];
            
            // $download = new FileDownloader;
            // $result = $download->downloadSurveys($data);

            $downloader = new LargeFileDownloader($data['urls']);
            if ($downloader->downloadAndZip()) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="propeller_files.zip"');
                header('Content-Length: ' . filesize('propeller_files.zip'));
                readfile('propeller_files.zip');
                unlink('propeller_files.zip');
            } else {
                http_response_code(500);
                echo json_encode(['error' => "Error creating the ZIP"]);
            }
            exit;
            return [
                'template' => 'homepage',
            ];
        }
        
        // Si ce n'est pas un POST, rediriger vers la page d'accueil
        header('Location: /');
        exit;
    }
}

