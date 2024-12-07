<?php
namespace App\Model;

require_once __DIR__ . '/../../vendor/autoload.php';
use DateTime;

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

    public function compare()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $organization = $data['subLot'];
        $date = $data['date'];

        $compare=$this->allSurveys($organization, $date);

        return json_encode($compare);
    }

    public function allSurveys($organization, $date)
    {
        $datetime = new DateTime($date);
        $iso = $datetime->format('Y-m-d\TH:i:s\Z');
        $dateFormatted = str_replace(":", "%3A", $iso);

        $allSurveys = [];
        
        $sitesData = $this->getSites($organization);
        foreach ($sitesData['results'] as $site){
            $surveys = $this->getSurveys($organization, $site['id'], $dateFormatted);
            
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
    public function getSurveys($organization, $site, $date)
    {
        $now = new DateTime();
        $nowFormatted = str_replace(":", "%3A", $now->format('Y-m-d\TH:i:s\Z'));

        $uri = $this->uriBase.$organization.'/sites/'.$site.'/surveys?date_captured_lt='.$nowFormatted.'&date_captured_gt='.$date;
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


    // get the environment variable
    private static function getEnv($key, $default = null)
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }


}