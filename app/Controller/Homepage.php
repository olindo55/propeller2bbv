<?php

namespace App\Controller;

use App\Model\Propeller;

class Homepage
{
    public function home()
    {
        return [
            'template' => 'homepage',
        ];
    }

    public function checkToken()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Unauthorised method');
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['data'] ?? '';

        // data validation
        if (strlen($token) !== 47) {
            http_response_code(400);
            exit('invalid lengh : '.var_dump($token));
        }

        $token = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        $token = strip_tags($token);

        if (!preg_match('/^Bearer [a-zA-Z0-9-]+$/', $token)) {
            http_response_code(400);
            exit('Invalid format');
        }

        $propeller = new Propeller;
        $checkToken = $propeller->checkToken($token);

        header('Content-Type: application/json');
        if ($checkToken['success']) {
            $_SESSION['propellerToken'] = $token;
            $_SESSION['flash_message'] = 'Valid token';
            $_SESSION['flash_alert'] = 'success';
            echo json_encode([
                'success' => true,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
        }
        exit();
    }
}