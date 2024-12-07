
<?php
    $flashMessage = '';
    if (isset($_SESSION['flash_message'])) {
        $flashMessage = $_SESSION['flash_message'];
        $flashAlert =$_SESSION['flash_alert'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_alert']);
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Propeller Synchronization Solution - Automated data transfer to your infrastructure."/>
    <title>Propeller Data transfert for BBV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Style -->
    <link rel="stylesheet" href="/asset/css/style.css">
</head>
    <body>
        <header>
            <nav class="navbar navbar-expand-xl my-navbar" data-bs-theme="dark">
                <div class="container-fluid">
                    <!-- Logo -->
                    <a id="logo" href="/">
                        <img src="/asset/images/logo/LogoBBV-HS2.png" alt="Logo of Balfour Beatty Vinci">
                        <h1>Propeller Data transfert for BBV</h1>
                    </a>
                </div>
            </nav>
        </header>

        <main class="container">
            <!-- inclure mon contenu -->
            <?php require_once $page ?>

            <!-- Toast -->
            <div  role="alert" aria-live="assertive" aria-atomic="true" class="position-fixed top-0 end-0 mt-5 p-3">
                <div id="toast-container">
                    <!-- Toasts will be dynamically added by php -->
                </div>
            </div>

            <!-- Modal -->
            <div class="modal" id="confirmModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalBasicLabel">Confirmation</h5>
                            <button type="button" class="btn-close" id="modal-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            ...
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary" id="cancelButton" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-danger" id="confirmButton">...</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Spinner -->
            <div id="spinner-container" class="d-none" >
                <div class="spinner-border mx-auto text-danger" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </main>
        
        <footer class="bg-light">
            <div id="software">DataSync Propeller 1.0 - dec. 2024</div>
            <a id="contact" href="mailto:julien.martinati@balfourbeattyvinci.com" target="_blank">julien.martinati@balfourbeattyvinci.com</a>
        </footer>
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/5c652914c5.js" crossorigin="anonymous"></script>
        <?php echo '<script type="module" src="'.$jsFile.'"></script>' ;?>
        <script type="text/javascript"> // Variables PHP injectées dans le JS
            const flashMessage = <?php echo json_encode($flashMessage); ?>;
            const flashAlert = <?php echo json_encode($flashAlert); ?>;
            </script>
        <script type="module" src="../../asset/js/base.js"></script>
    </body>
</html>