<?php

namespace App\Controller;

use App\Model\Propeller;

class SurveyList
{
    public function view()
    {
        if(isset($_SESSION['propellerToken'])){
            return [
                'template' => 'surveyList',
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

            $propeller = new Propeller;
            $finalZipName = 'download_from_propeller.zip';
            $finalZip = new \ZipArchive();
            
            if ($finalZip->open($finalZipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception("Impossible de créer l'archive ZIP finale");
            }

            foreach ($surveys['data'] as $survey) {
                // Get data by survey
                $organization_id = $survey['organization_id'];
                $site_id = $survey['site_id'];
                $survey_id = $survey['survey_id'];
                $site = $survey['site'];
                $name = $survey['name'];
                $date_captured = $survey['date_captured'];

                $date = strtotime($date_captured);
                $dateFormatted = date('Ymd', $date);

                // prepare the zipname for this survey
                $zipname = $this->cleanName($site . '_' . $dateFormatted . '_' . $name) . '.zip';

                // get file's data and download files
                $filesData = $propeller->getFilesList($organization_id, $site_id, $survey_id);
                $surveyDownloaded = $propeller->downloadFiles($name, $filesData);

                // Create individual ZIP for this survey
                $surveyZip = new \ZipArchive();
                if ($surveyZip->open($zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new \Exception("Impossible de créer l'archive ZIP pour " . $name);
                }

                // Add files to survey ZIP
                foreach ($surveyDownloaded as $file) {
                    if (file_exists($file)) {
                        $surveyZip->addFile($file, basename($file));
                    }
                }
                
                $surveyZip->close();

                // Clean up downloaded files
                foreach ($surveyDownloaded as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                // Add survey ZIP to final ZIP
                if (file_exists($zipname)) {
                    $finalZip->addFile($zipname, $zipname);
                }
            }

            $finalZip->close();
            rmdir('temp_downloads');

            // Clean up individual ZIP files after adding them to final ZIP
            foreach ($surveys['data'] as $survey) {
                $date = strtotime($survey['date_captured']);
                $dateFormatted = date('Ymd', $date);
                $zipname = $this->cleanName($survey['site'] . '_' . $dateFormatted . '_' . $survey['name']) . '.zip';
                if (file_exists($zipname)) {
                    unlink($zipname);
                }
            }

            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Send the final ZIP file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($finalZipName) . '"');
            header('Content-Length: ' . filesize($finalZipName));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            readfile($finalZipName);
            unlink($finalZipName);
            exit;
        }
        
        header('Location: /');
        exit;
    }

    private function cleanName($name) {
        $clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $clean = preg_replace('/_+/', '_', $clean);
        return trim($clean, '_');
    }
}