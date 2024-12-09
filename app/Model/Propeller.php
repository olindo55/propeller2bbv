<?php
namespace App\Model;

require_once __DIR__ . '/../../vendor/autoload.php';

use DateTime;
use GuzzleHttp\Client;
use ZipArchive;

// Configuration
ini_set('max_execution_time', 300);
ini_set('memory_limit', '8G');
ini_set('post_max_size', '8G');
ini_set('upload_max_filesize', '8G');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

function logMessage($message) {
    date_default_timezone_set('UTC');
    $timestamp = date('Y-m-d H:i:s');
    error_log("$message");
    echo "[$timestamp] $message<br>";
}

class Propeller
{
    private $client;
    private $uriBase = 'https://api.propelleraero.com/v1/organizations/';

    public function __construct()
    {
        if ($this->client === null) {
            $this->client = new \GuzzleHttp\Client();
        }
    }

    function downloadFiles($filesData) {
        logMessage("Début du téléchargement...");
        
        $temp_dir = 'temp_downloads/';
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0777, true);
            logMessage("Dossier temporaire créé: " . realpath($temp_dir));
        }
        
        $files = [];
        $index = -1;

        foreach ($filesData as $file){
            $index += 1;
            $filename = $file['name']. '.' . $file['format'];
            $url = $file['url'];
            $size = $file['size_bytes'];

            $filepath = $temp_dir . $filename;

            logMessage("Téléchargement du fichier " . ($index + 1) . "/" . count($filesData));

            try {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour les URLs HTTPS
                $content = curl_exec($ch);
                
                if ($content === false) {
                    logMessage("ERREUR CURL: " . curl_error($ch));
                    continue;
                }
                
                $filesize = file_put_contents($filepath, $content);
                if ($filesize === false) {
                    logMessage("ERREUR: Impossible de sauvegarder $filename");
                    continue;
                }
                
                logMessage("Fichier sauvegardé: $filename (Taille: " . $filesize . " bytes)");
                $files[] = $filepath;
                
                curl_close($ch);
                
            } catch (Exception $e) {
                logMessage("ERREUR: " . $e->getMessage());
            }
        }
        
        return $files;
    }

    function createZip($files, $zipname) {
        logMessage("Création du ZIP: " . $zipname);
        
        $zip = new ZipArchive();
        if ($zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                    logMessage("Fichier ajouté au ZIP: " . basename($file));
                }
            }
            $zip->close();
            
            // Nettoyage
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            rmdir('temp_downloads');
            
            return true;
        }
        return false;
    }

    public function formattedDate($date){
        $datetime = new DateTime($date);
        $iso = $datetime->format('Y-m-d\TH:i:s\Z');
        return str_replace(":", "%3A", $iso);
    }

    public function surveyList($organization, $from, $to)
    {
        $from = $this->formattedDate($from);
        $to = $this->formattedDate($to);

        $allSurveys = [];
        
        $sitesData = $this->getSites($organization);
        if (isset($sitesData['results'])) {
            foreach ($sitesData['results'] as $site){
                $surveys = $this->getSurveys($organization, $site['id'], $from, $to);
                
                foreach($surveys['results'] as $survey){
                    $data['organization_id'] = $organization;
                    $data['survey_id'] = $survey['id'];
                    $data['site'] = $site['name'];
                    $data['name'] = $survey['name'];
                    $data['date_captured'] = date('d/m/Y', strtotime($survey['date_captured']));
                    $data['site_id'] = $site['id'];

                    $allSurveys[] = $data;
                }
            }
            return $allSurveys;
        }
    }

    public function checkToken($token){
        try {
            $response = $this->client->request('GET', $this->uriBase, [
                'headers' => [
                    'Authorization' => $token,
                    'accept' => 'application/json',
                ],
            ]);
            return [
                'success' => true,
                'message' => 'Token valid'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Invalid token'
            ];
        }
    }

    public function api($uri)
    {
        try {
            $response = $this->client->request('GET', $uri, [
                'headers' => [
                    'Authorization' => $_SESSION['propellerToken'],
                    'accept' => 'application/json',
                ],
            ]);
            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la requête API: " . $e->getMessage());
        }
    }

    /**
     * Get list of organizations (SubLot)
     * 
     * @return string JSON formaté contenant les informations suivantes :
     *    {
     *      "id": 123,
     *      "name": "Jean Dupont",
     *    }
     */
    public function getOrganizations()
    {
        $response = $this->api($this->uriBase);
        return $response;
    }

    /**
     * Get list of sites by organization
     *
     * @param string $organization id of organization
     * 
     * @return string JSON formaté contenant les informations suivantes :
     *    {
     *      "id": 123,
     *      "name": 10 - Ashow Rd to Finham Brook_,
     *      "date_created": 2021-11-18T08:27:33.915030Z,
     *    }
     */
    public function getSites($organization)
    {
        $uri = $this->uriBase.$organization.'/sites';
        $response = $this->api($uri);
        return $response;
    }

    /**
     * Get list of surveys by site
     *
     * @param string $organization id of organization
     * @param string $site id of site
     * @param string $date  Date representing when user wants to retrieve data
     * 
     * @return string JSON formaté contenant les informations suivantes :
     *    {
     *      "id": 123,
     *      "date_captured": 2024-11-29T08:05:27Z,
     *      "date_submitted": 2024-11-29T14:18:56.560535Z,
     *      "name": 241129 A46 Compound (West),
     *    }
     */
    public function getSurveys($organization, $site, $from, $to)
    {
        // $now = new DateTime();
        // $nowFormatted = str_replace(":", "%3A", $now->format('Y-m-d\TH:i:s\Z'));

        $uri = $this->uriBase.$organization.'/sites/'.$site.'/surveys?date_captured_lt='.$to.'&date_captured_gt='.$from;
        $response = $this->api($uri);
        return $response;

    }

    /**
     * Get list of files by survey
     *
     * @param string $organization id of organization
     * @param string $site id of site
     * @param string $survey id of survey
     * 
     * @return string JSON formaté contenant les informations suivantes :
     *    {
     *      "name": "DSM TIFF (Local Grid)",
     *      "is_wgs84": false,
     *      "type": "terrain_dsm",
     *      "format": "tiff",
     *      "size_bytes": 282084363,
     *      "url": "https://srv-01-eu-west-1.data.propelleraero.com/ob1a160b21/pqb8fa95b4_site_dsm.tiff?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iMWExNjBiMjEvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=xa8aWlOpfsbe3c4zqFz9L4Y35jcSmXXKxW2VIb-E0-U286BtQ2ROcNyD1ghku~Ga~TN06nxNj~ZpyegQotLevHgSyg8t1MzW5VRl8xS-WT8xQ6Mpum9D2FA6PwQbqAXr1kFxVbNkWE3oAUgzehFhIR6OiiLnImabye~RqJ5btmS6Hhrcwpm4TiJVm1YUxzNVGhX4WPncwTrsDZl3UUU-olHAVR8PxqKBxb3WX5NWDqccviwyH1ZIQwQdpqNSWJlvJPL9NHRFxv-GRS14t6JmBqAl38Hxq9e5ZwuRluqz0ViiYCSScmCmBR7wU9qFZFkmPFUzT4tdFeMMwH1OAtjzDg__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000"
     *    }
     */
    public function getFilesList($organization, $site, $survey)
    {
        $uri = $this->uriBase.$organization.'/sites/'.$site.'/surveys/'.$survey.'/files';
        $response = $this->api($uri);
        return $response;
    }



}