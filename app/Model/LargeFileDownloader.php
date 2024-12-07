<?php

namespace App\Model;

use ZipArchive;
use Exception;

set_time_limit(0);
ini_set('memory_limit', '1024M');

class LargeFileDownloader {
    private $urls = [];
    private $tempDir;
    private $zipName;
    private $sessionId;

    public function __construct($urls, $zipName = 'propeller_files.zip') {
        $this->urls = $urls;
        $this->zipName = $zipName;
        $this->tempDir = sys_get_temp_dir() . '/temp_' . uniqid();
        $this->sessionId = session_id();
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir);
        }
    }

    public function downloadAndZip() {
        try {
            $totalFiles = count($this->urls);
            $files = [];
            
            // Initialise la progression
            $this->updateProgress(0, "Start downloading");
            
            // Télécharge chaque fichier
            foreach ($this->urls as $index => $url) {
                $fileName = $this->tempDir . '/file_' . ($index + 1) . '.pts';
                $this->updateProgress(
                    ($index / $totalFiles) * 90, 
                    "Download file" . ($index + 1) . " of " . $totalFiles
                );
                
                $this->downloadFile($url, $fileName);
                $files[] = $fileName;
            }

            // Crée le ZIP
            $this->updateProgress(90, "Creating the ZIP file");
            $zip = new ZipArchive();
            if ($zip->open($this->zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($files as $index => $file) {
                    $zip->addFile($file, basename($file));
                    $this->updateProgress(
                        90 + (($index / count($files)) * 10),
                        "Add to ZIP : " . basename($file)
                    );
                }
                $zip->close();
            }

            // Nettoie
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->tempDir);

            $this->updateProgress(100, "Completed");
            return true;
        } catch (Exception $e) {
            $this->updateProgress(-1, "Error: " . $e->getMessage());
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }

    private function downloadFile($url, $fileName) {
        $fp = fopen($fileName, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function(
            $downloadSize, $downloaded, $uploadSize, $uploaded
        ) use ($url) {
            if ($downloadSize > 0) {
                $progress = ($downloaded / $downloadSize) * 100;
                $this->updateProgress(null, "Download " . basename($url) . ": " . 
                    round($progress, 1) . "%");
            }
        });
        
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    private function updateProgress($percent, $message) {
        $progressFile = sys_get_temp_dir() . '/progress_' . $this->sessionId . '.json';
        $progress = [
            'percent' => $percent,
            'message' => $message,
            'timestamp' => time()
        ];
        file_put_contents($progressFile, json_encode($progress));
    }
}

// Point d'entrée pour le téléchargement
if ($_POST['action'] === 'createZip') {
    $urls = $_POST['urls'];
    
    $downloader = new LargeFileDownloader($urls);
    if ($downloader->downloadAndZip()) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="propeller_files.zip"');
        header('Content-Length: ' . filesize('propeller_files.zip'));
        readfile('propeller_files.zip');
        unlink('propeller_files.zip');
    } else {
        http_response_code(500);
        echo "Error creating the ZIP";
    }
    exit;
}

// // Point d'entrée pour vérifier la progression
// if ($_GET['action'] === 'checkProgress') {
//     session_start();
//     $progressFile = sys_get_temp_dir() . '/progress_' . session_id() . '.json';
//     if (file_exists($progressFile)) {
//         echo file_get_contents($progressFile);
//         if ($_GET['cleanup'] === 'true') {
//             unlink($progressFile);
//         }
//     } else {
//         echo json_encode(['percent' => 0, 'message' => 'En attente...']);
//     }
//     exit;
// }
?>