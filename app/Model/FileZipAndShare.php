<?php
namespace App\Model;

use ZipArchive;
use PHPMailer;
use Propeller;

class FileZipAndShare {
    private $zipPath;
    private $downloadUrl;
    private $emailConfig;

    public function __construct($zipPath, $downloadUrl, $emailConfig = []) {
        $this->zipPath = $zipPath;
        $this->downloadUrl = $downloadUrl;
        $this->emailConfig = array_merge([
            'from' => 'sender@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_user' => 'user',
            'smtp_pass' => 'password'
        ], $emailConfig);
    }

    public function createZip($files, $zipName) {
        $zip = new ZipArchive();
        $zipFullPath = $this->zipPath . '/' . $zipName;

        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Impossible de créer le fichier zip");
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                // Ajoute le fichier en gardant uniquement le nom du fichier, pas le chemin complet
                $zip->addFile($file, basename($file));
            }
        }

        $zip->close();
        return $zipFullPath;
    }

    public function sendEmail($to, $zipName) {
        // Utilisation de PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur
            $mail->isSMTP();
            $mail->Host = $this->emailConfig['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->emailConfig['smtp_user'];
            $mail->Password = $this->emailConfig['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->emailConfig['smtp_port'];

            // Destinataires
            $mail->setFrom($this->emailConfig['from']);
            $mail->addAddress($to);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Votre fichier est prêt à être téléchargé';
            
            $downloadLink = $this->downloadUrl . '/' . $zipName;
            
            $mail->Body = "Bonjour,<br><br>
                         Votre fichier est prêt à être téléchargé.<br>
                         Cliquez sur le lien suivant pour le télécharger : 
                         <a href='{$downloadLink}'>{$downloadLink}</a><br><br>
                         Ce lien expirera dans 24 heures.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo);
        }
    }
}

// Exemple d'utilisation
try {
    $config = [
        'from' => 'votre@email.com',
        'smtp_host' => 'smtp.votreserveur.com',
        'smtp_port' => 587,
        'smtp_user' => 'utilisateur',
        'smtp_pass' => 'motdepasse'
    ];

    $handler = new FileZipAndShare(
        '/chemin/vers/dossier/zip',
        'https://votreserveur.com/downloads',
        $config
    );

    // Liste des fichiers à zipper
    $files = [
        '/chemin/vers/fichier1.pdf',
        '/chemin/vers/fichier2.jpg'
    ];

    // Création du zip
    $zipName = 'archive_' . date('Y-m-d_His') . '.zip';
    $zipPath = $handler->createZip($files, $zipName);

    // Envoi de l'email
    $handler->sendEmail('destinataire@email.com', $zipName);

    echo "Le zip a été créé et l'email envoyé avec succès !";
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}