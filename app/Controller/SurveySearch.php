<?php

namespace App\Controller;

use App\Model\Propeller;

class SurveySearch
{
    public function view()
    {
        if(isset($_SESSION['propellerToken'])){

            $propeller = new Propeller;
            $listSubLot = $propeller->getOrganizations();
    
            return [
                'template' => 'surveySearch',
                'sublot'=> $listSubLot,
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

    public function find()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer et valider les données
            $sublotId = $_POST['sublot_id'] ?? null;
            $fromDate = $_POST['from_date'] ?? null;
            
            // Validation
            if (!$sublotId || !$fromDate) {
                return [
                    'template' => 'error',
                    'error' => 'Missing required fields'
                ];
            }
            
            // Appeler votre modèle pour traiter les données
            $propeller = new Propeller;
            $result = $propeller->allSurveys($sublotId, $fromDate);
            // var_dump($result);
            return [
                'template' => 'surveysList',
                'surveys' => $result
            ];
        }
        
        // Si ce n'est pas un POST, rediriger vers la page d'accueil
        header('Location: /');
        exit;
    }

}