<?php
// Starte die PHP-Session
session_start();

// Überprüfen, ob eine Sprache ausgewählt wurde
if (isset($_GET['language'])) {
    $selectedLanguage = $_GET['language'];

    // Setze die ausgewählte Sprache in der Session
    $_SESSION['lang'] = $selectedLanguage;
}
?>




<footer class="position-relative" id="footer-main">
    <div class="footer pt-lg-1 footer-dark bg-dark">
        <div class="container pt-4">

            <hr class="divider divider-fade divider-dark my-4">
            <div class="row align-items-center justify-content-md-between pb-4">
                <div class="col-md-4">
                    <span class="text-muted">
                        <?= str_replace('{websiteName}', '' . $name . '', $translations['footer']['content']) ?>

                        </a>

                    </span>
                </div>

                <div class="col-md-4">
                    <div class="copyright text-sm font-weight-bold text-center">
                        <div class="dropdown">
                            <?php
                            $currentLanguage = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'de_DE';

                            $flagUrls = [
                                'de_DE' => $assetspath . 'style/flags/de.svg',
                                'en_US' => $assetspath . 'style/flags/en.svg',
								'fr_FR' => $assetspath . 'style/flags/fr.svg',
                            ];

                            $currentFlagUrl = isset($flagUrls[$currentLanguage]) ? $flagUrls[$currentLanguage] : '';
                            $currentLanguageName = $translations['footer'][$currentLanguage];
                            ?>
                            <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" data-offset="0,10">
                                <img alt="<?= $currentLanguageName ?>" loading="lazy"
                                    title="<?= $currentLanguageName ?>" src="<?= $currentFlagUrl ?>">
                                <span class="d-none d-lg-inline-block">
                                    <?= str_replace('{websiteName}', $name, ($currentLanguage == 'de_DE') ? $translations['footer']['german'] : (($currentLanguage == 'en_US') ? $translations['footer']['english'] : $translations['footer']['french'])) ?>

                                </span>
                                <span class="d-lg-none">
                                    <?= ($currentLanguage == 'de_DE') ? $translations['footer']['german'] : (($currentLanguage == 'en_US') ? $translations['footer']['english'] : $translations['footer']['french']) ?>

                                </span>
                                <span class="d-lg-none">
                                    <?= $currentLanguageName ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu text-black" style="">
                                <li>
                                    <a class="dropdown-item text-black" title="de_DE" href="#"
                                        onclick="setLanguage('de_DE')">
                                        <img alt="Deutsch" loading="lazy" title=""
                                            src="<?= $assetspath ?>style/flags/de.svg">
                                        <span>
                                            <?= str_replace('{websiteName}', $name, $translations['footer']['german']) ?>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-black" title="en_US" href="#"
                                        onclick="setLanguage('en_US')">
                                        <img alt="English" loading="lazy" title=""
                                            src="<?= $assetspath ?>style/flags/en.svg">
                                        <span>
                                            <?= str_replace('{websiteName}', $name, $translations['footer']['english']) ?>
                                        </span>
                                    </a>
									
                                    <a class="dropdown-item text-black" title="fr_FR" href="#"
                                        onclick="setLanguage('fr_FR')">
                                        <img alt="France" loading="lazy" title=""
                                            src="<?= $assetspath ?>style/flags/fr.svg">
                                        <span>
                                            <?= str_replace('{websiteName}', $name, $translations['footer']['french']) ?>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <script>
                    function setLanguage(language) {
                        document.cookie = "lang=" + language + "; path=/";
                        location.reload();
                    }
                </script>


                <div class="col-md-4">
                    <div class="copyright font-weight-bold text-center text-md-right text-muted"><a target=" _blank"
                            class="font-weight-bold"><span class="text-muted"><i class="fa-duotone fa-gear fa-spin"></i> </span>RMS</a>
                        <span class="text-muted">
                            <?= $siteversion ?>
                        </span>

                        </a>

                        </span>

<?php
// Startzeit erfassen
$startTime = $_SERVER['REQUEST_TIME_FLOAT'];

// Hier kommt der restliche HTML-Code deiner Seite

// Endzeit erfassen
$endTime = microtime(true);

// Ladezeit berechnen (in Sekunden)
$loadTime = $endTime - $startTime;

// Ladezeit auf zwei Dezimalstellen runden
$loadTime = number_format($loadTime, 2);

// Ladezeit im Footer anzeigen
echo '<div>' . str_replace('{load}', $loadTime, $translations['footer']['loaded']) . '</div>';
?>
                    </div>
                </div>
            </div>
        </div>
</footer>

<style>
    html {
        height: 100%;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    #footer-main {
        margin-top: auto;
    }
</style>

<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>