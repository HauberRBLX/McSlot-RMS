<?php

function get_client_ip()
{
    $ipAddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    return $ipAddress;
}


include 'db_connection.php';
include 'settings/config.php';
include 'settings/head.php';

?>

<title>Verifizierung &mdash; <?= $name ?></title>
<br><br>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card mb-n7 position-relative zindex-100">
            <h1 class="text-center mt-5">E-Mail Verifizierung</h1>
            <div class="card-body px-5">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center">
                            <?php
                            if (!empty($alertMessage)) {
                                echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show" role="alert">
                                          ' . $alertMessage . '
                                      </div>';
                            }
                            ?>
                            <p class="text opacity-8">Bitte gebe den Verifizierungs-Code ein:</p>
                            <br>
                            <form method="post" id="otpForm">
                                <div class="form-row justify-content-center" id="otpContainer">
                                    <!-- Separate input fields for each digit -->
<div class="form-row justify-content-center" id="otpContainer">
    <?php
    $numberOfDigits = 6;
    for ($i = 1; $i <= $numberOfDigits; $i++) {
        echo '
            <div class="form-group col-auto">
                <input type="text" class="form-control mailverify-input" maxlength="1" id="digit' . $i . '" name="mailverify' . $i . '" required>
            </div>
        ';
    }
    ?>
</div>
                                </div>
                                <br>
                                <input type="submit" value="Überprüfen" class="btn btn-primary">
                            </form>
                            <form method="post">
                                <input type="hidden" name="logout" value="true">
                            </form>
                            <br><br>
                            <p class="text opacity-8">ID: <?= $key ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script>
$(document).ready(function () {
    // Eingabe in die OTP-Felder steuern
    $('.mailverify-input').on('input', function () {
        var $this = $(this);
        var maxLength = parseInt($this.attr('maxlength'));
        var sanitizedValue = $this.val().replace(/\D/g, ''); // Nur Zahlen zulassen

        if (sanitizedValue.length > maxLength) {
            sanitizedValue = sanitizedValue.slice(0, maxLength); // Maximal zulässige Länge begrenzen
        }

        $this.val(sanitizedValue); // Gesäuberten Wert in das Feld setzen

        if (sanitizedValue.length === maxLength) {
            // Finde das nächste Eingabefeld
            var $nextInput = $this.closest('.form-group').next().find('.mailverify-input');

            if ($nextInput.length > 0) {
                $nextInput.focus(); // Fokus auf das nächste Eingabefeld setzen
            } else {
                $this.blur(); // Fokus aus dem aktuellen Feld entfernen
            }
        }
    });

    // Eingefügten Text aus der Zwischenablage verarbeiten
    $('.mailverify-input').on('paste', function (event) {
        var clipboardData = (event.originalEvent || event).clipboardData;
        var pastedText = clipboardData.getData('text');
        var sanitizedText = pastedText.replace(/\D/g, ''); // Nur Zahlen zulassen

        if (/^\d{6}$/.test(sanitizedText)) {
            // Falls der eingefügte Text 6 Zahlen enthält, fülle die Felder entsprechend
            var digits = sanitizedText.split('');
            $('.mailverify-input').each(function (index) {
                $(this).val(digits[index]);
            });
        }

        event.preventDefault(); // Standardverhalten des Einfügens unterdrücken
    });

    // Navigation beim Klick oder Touch auf ein OTP-Feld
    $('.mailverify-input').on('touchstart', function () {
        var $this = $(this);
        var maxLength = parseInt($this.attr('maxlength'));

        // Falls das Feld leer ist, entferne den Inhalt aller folgenden Felder
        if ($this.val() === '') {
            $('.mailverify-input').slice($('.mailverify-input').index($this), 6).val('');
        }
    });

    $('.mailverify-input').on('touchend', function () {
        var $this = $(this);
        var maxLength = parseInt($this.attr('maxlength'));
        var currentIndex = $('.mailverify-input').index($this);

        // Fokus auf das aktuelle Feld setzen
        $this.focus();
    });
});




</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <style>
        .mailverify-input {
            width: 60px;
            height: 60px;
            font-size: 20px;
            text-align: center;
            padding: 10px;
        }
    </style>

<br><br><br><br><br><br>

<?php 
include 'settings/footer.php'; 
closeConnection($conn); // Schließe die Datenbankverbindung
?>
