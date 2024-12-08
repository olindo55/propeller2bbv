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
            $sublotId = $_POST['sublot_id'] ?? null;
            $fromDate = $_POST['from_date'] ?? null;
            $toDate = $_POST['to_date'] ?? null;
            
            // header('Content-Type: application/json');

            // Validation
            if (!$sublotId || !$fromDate || !$toDate) {
                echo json_encode([
                    'template' => 'surveySearch',
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
            }
            if ($fromDate > $toDate){
                echo json_encode([
                    'template' => 'surveySearch',
                    'success' => false,
                    'message' => 'Date error'
                ]);
            }
            
            // Call model to get the list of surveys
            $propeller = new Propeller;
            $result = $propeller->surveyList($sublotId, $fromDate, $toDate);

            $_SESSION['surveyData'] = [
                'surveys' => $result
            ];
            echo json_encode([
                'success' => true,
            ]);
        }
        exit();
    }
}