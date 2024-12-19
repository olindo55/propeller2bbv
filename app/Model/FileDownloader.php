<?php
namespace App\Model;

use App\Model\Propeller;
use PhpZip\ZipFile; 
use DateTime;

// ini_set('memory_limit', '10G');

class FileDownloader {
    private $tempDir;
    private $downloadedFiles = [];
    
    public function __construct($tempDir = 'temp') {
        $this->tempDir = $tempDir;
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
    }

    public function downloadSurveys($surveys) {
        $urlList = [];
        foreach ($surveys as $survey){
            // Define the name of the zipfile
            $date_string = $survey['date_captured'];
            $formatted_date = DateTime::createFromFormat('d/m/Y', $date_string)->format('Ymd');
            $filename = $survey['site'].'_'.$formatted_date.'_'.$survey['name'].'.zip'; // Ajout de l'extension .zip
            
            // Get the files id
            $propeller = new Propeller;
            $files = $propeller->getFilesList($survey['organization_id'], $survey['site_id'], $survey['id']);
            
            foreach ($files['results'] as $file){
                array_push($urlList, $file['url']);// array_push($urlList, 'https://srv-01-eu-west-1.data.propelleraero.com/ob6b868501/f2ab5283-eeda-42dd-b859-6c8f3a72703f.laz?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iNmI4Njg1MDEvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=mlyxkjgi1rWttQJt8UrRHk999ISgbkbqKRnB1JHjoVThxhbskWI6z9mo39XSKU9kXvTvUtGKiqLdM5Xqs-3IX-k4TY8XropepLyCyM5b0TapJjNMFhFLr0tHqw3hGD-~Ni1FaYNg6H0fO4X~lZdJm2KKBPVdZHX7ih0ET~hMUeJZwNRw0UxG2VivC4bpcYoqkLRS7VNXD67vE99nqnJmeWsLYtqWfNH6Uk7cOiVcFMvfcagoWixaeYvgVXj-o72mNYlOU14IWE3ojEZMlAsMmbblJ7ETgboJtOW3D1s8G~gd2YQWB0E3ai-Y~zqSrXjYk2JKEGqp-CLFLmXLQvUqig__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000');
            }
            
            // Create zip
            $result = $this->downloadMultipleFiles($urlList, $filename);
        }
        return $result;
    }

    public function downloadMultipleFiles($urls, $zipName = 'files.zip') {
        // Crée un zip temporaire
        $zip = new ZipFile();
        
        // Headers pour forcer le téléchargement
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        try {
            // Ajoute chaque fichier distant au zip
            foreach ($urls as $url) {
                // Récupère le nom du fichier depuis l'URL
                $filename = basename(parse_url($url, PHP_URL_PATH));
                
                // Récupère le contenu du fichier distant
                $fileContent = file_get_contents($url);
                if ($fileContent !== false) {
                    // Ajoute le fichier au zip
                    $zip->addFromString($filename, $fileContent);
                }
            }
            
            // Envoie le zip au navigateur
            echo $zip->outputAsString();
            
            // Ferme le zip
            $zip->close();
            
            exit;
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo "Erreur lors de la création du zip : " . $e->getMessage();
            exit;
        }
    }


    public function downloadFiles($urls) {
        foreach ($urls as $url) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            if (empty($filename)) {
                $filename = md5($url) . '.tmp';
            }
            
            $localPath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;
            
            try {
                // Ouvre un flux de lecture depuis l'URL
                $remoteStream = fopen($url, 'rb');
                if ($remoteStream) {
                    // Ouvre un flux d'écriture vers le fichier local
                    $localStream = fopen($localPath, 'wb');
                    if ($localStream) {
                        // Copie le contenu par morceaux de 8KB
                        while (!feof($remoteStream)) {
                            $chunk = fread($remoteStream, 8192);
                            fwrite($localStream, $chunk);
                        }
                        
                        // Ferme les flux
                        fclose($localStream);
                        fclose($remoteStream);
                        
                        $this->downloadedFiles[] = $localPath;
                    }
                }
            } catch (\Exception $e) {
                error_log("Erreur lors du téléchargement de " . $url . ": " . $e->getMessage());
                // Si les flux sont encore ouverts, on les ferme
                if (isset($localStream) && is_resource($localStream)) {
                    fclose($localStream);
                }
                if (isset($remoteStream) && is_resource($remoteStream)) {
                    fclose($remoteStream);
                }
            }
        }
    }
    
    public function createZip($zipName) {
        try {
            $zipFile = new ZipFile();
           
            // Ajoute chaque fichier à l'archive
            foreach ($this->downloadedFiles as $file) {
                if (file_exists($file)) {
                    $zipFile->addFile($file, basename($file));
                }
            }
           
            // Sauvegarde et ferme l'archive
            $zipFile->saveAsFile($zipName);
            $zipFile->close();
            
            // Vide la liste des fichiers après création du zip
            $this->downloadedFiles = [];
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function cleanup() {
        // Supprime les fichiers temporaires
        foreach ($this->downloadedFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Supprime le dossier temporaire s'il est vide
        if (is_dir($this->tempDir) && count(scandir($this->tempDir)) === 2) {
            rmdir($this->tempDir);
        }
    }
}
?>

<!-- // Exemple d'utilisation
$downloader = new FileDownloader('temp_downloads');

// Liste des URLs à télécharger
$urls = [
    'http://exemple.com/fichier1.pdf',
    'http://exemple.com/fichier2.jpg'
];

try {
    $downloader->downloadFiles($urls);
    
    if ($downloader->createZip('archives.zip')) {
        echo "Archive ZIP créée avec succès !";
    } else {
        echo "Erreur lors de la création de l'archive ZIP";
    }
    
    // Nettoie les fichiers temporaires
    $downloader->cleanup();
    
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
} -->
