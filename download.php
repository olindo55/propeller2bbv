<?php
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

// Configuration
ini_set('max_execution_time', 300);
ini_set('memory_limit', '8G');
ini_set('post_max_size', '8G');
ini_set('upload_max_filesize', '8G');

function downloadFiles($urls) {
    logMessage("Début du téléchargement...");
    
    $temp_dir = 'temp_downloads/';
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0777, true);
        logMessage("Dossier temporaire créé: " . realpath($temp_dir));
    }
    
    $files = [];
    foreach ($urls as $index => $url) {
        $filename = basename(parse_url($url, PHP_URL_PATH));
        $filename = preg_replace('/\?.*/', '', $filename);
        $filepath = $temp_dir . $filename;
        
        logMessage("Téléchargement du fichier " . ($index + 1) . "/" . count($urls));
        
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

// Traitement de la requête
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urls']) && isset($_POST['filename'])) {
    $urls = json_decode($_POST['urls'], true);
    $filename = json_decode($_POST['filename'], true);
    
    if (!empty($urls)) {
        $zipname = $filename . '.zip';
        $files = downloadFiles($urls);
        
        if (createZip($files, $zipname)) {
            // Nettoyage de tout output précédent
            ob_clean();
            ob_start();
            
            // Empêcher la mise en cache
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Headers pour fichier binaire
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($zipname).'"');
            header('Content-Length: ' . filesize($zipname));
            header('Content-Transfer-Encoding: binary');
            
            // Lecture et envoi du fichier binaire
            readfile($zipname);
            
            // Vider le buffer et l'envoyer
            ob_end_flush();
            
            // Nettoyage
            unlink($zipname);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléchargement de fichiers</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #4CAF50;
                 color: white; border: none; border-radius: 4px; margin-bottom: 20px; }
        button:hover { background-color: #45a049; }
        button:disabled { background-color: #cccccc; cursor: not-allowed; }
        #loading { display: none; margin-top: 20px; color: #666; }
        #log { margin-top: 20px; padding: 10px; border: 1px solid #ddd;
               background-color: #f9f9f9; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>Téléchargement de fichiers</h1>
    
    <form method="post" id="downloadForm">
        <button type="submit" id="downloadBtn">Télécharger tous les fichiers (ZIP)</button>
    </form>
    
    <div id="loading">Téléchargement en cours, veuillez patienter...</div>
    <div id="log"></div>

    <script>
        document.getElementById('downloadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('downloadBtn');
            const loading = document.getElementById('loading');
            
            const urls = [
                // 'https://srv-01-eu-west-1.data.propelleraero.com/ob1a160b21/pqb8fa95b4_site_dsm.tiff?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iMWExNjBiMjEvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=xa8aWlOpfsbe3c4zqFz9L4Y35jcSmXXKxW2VIb-E0-U286BtQ2ROcNyD1ghku~Ga~TN06nxNj~ZpyegQotLevHgSyg8t1MzW5VRl8xS-WT8xQ6Mpum9D2FA6PwQbqAXr1kFxVbNkWE3oAUgzehFhIR6OiiLnImabye~RqJ5btmS6Hhrcwpm4TiJVm1YUxzNVGhX4WPncwTrsDZl3UUU-olHAVR8PxqKBxb3WX5NWDqccviwyH1ZIQwQdpqNSWJlvJPL9NHRFxv-GRS14t6JmBqAl38Hxq9e5ZwuRluqz0ViiYCSScmCmBR7wU9qFZFkmPFUzT4tdFeMMwH1OAtjzDg__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ',
                'https://srv-01-eu-west-1.data.propelleraero.com/ob6b868501/f2ab5283-eeda-42dd-b859-6c8f3a72703f.laz?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iNmI4Njg1MDEvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=mlyxkjgi1rWttQJt8UrRHk999ISgbkbqKRnB1JHjoVThxhbskWI6z9mo39XSKU9kXvTvUtGKiqLdM5Xqs-3IX-k4TY8XropepLyCyM5b0TapJjNMFhFLr0tHqw3hGD-~Ni1FaYNg6H0fO4X~lZdJm2KKBPVdZHX7ih0ET~hMUeJZwNRw0UxG2VivC4bpcYoqkLRS7VNXD67vE99nqnJmeWsLYtqWfNH6Uk7cOiVcFMvfcagoWixaeYvgVXj-o72mNYlOU14IWE3ojEZMlAsMmbblJ7ETgboJtOW3D1s8G~gd2YQWB0E3ai-Y~zqSrXjYk2JKEGqp-CLFLmXLQvUqig__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ'
            ];

            btn.disabled = true;
            loading.style.display = 'block';

            try {
                const formData = new FormData();
                const filename = 'propeller';
                formData.append('urls', JSON.stringify(urls));
                formData.append('filename', JSON.stringify(filename));

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename + '.zip';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    console.error('Erreur lors du téléchargement');
                }
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                btn.disabled = false;
                loading.style.display = 'none';
            }
        });
    </script>
</body>
</html>