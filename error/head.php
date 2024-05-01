<head>
    <link rel="shortcut icon" type="image/x-icon" href="<?= $faviconurl ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <script>
        window.addEventListener("load", function () {
            setTimeout(function () {
                document.querySelector('body').classList.add('loaded');
            }, 150);
        });
    </script>
    <script src="https://kit.fontawesome.com/5178daf571.js?1234" crossorigin="anonymous"></script>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="<?= $assetspath ?>style/libs/swiper/dist/css/swiper.min.css">
    <link rel="stylesheet" href="<?= $assetspath ?>style/libs/@fancyapps/fancybox/dist/jquery.fancybox.min.css">
    <link rel="stylesheet" href="<?= $assetspath ?>style/libs/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= $assetspath ?>style/css/preloader.css" id="stylesheet">
    <link rel="stylesheet" href="<?= $assetspath ?>style/css/style.css" id="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/all.min.js"
        integrity="sha256-HkXXtFRaflZ7gjmpjGQBENGnq8NIno4SDNq/3DbkMgo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css"
        integrity="sha256-2XFplPlrFClt0bIdPgpz8H7ojnk10H69xRqd9+uTShA=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css"
        integrity="sha256-ENFZrbVzylNbgnXx0n3I1g//2WeO47XxoPe0vkp3NC8=" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"
        integrity="sha256-3blsJd4Hli/7wCQ+bmgXfOdK7p/ZUMtPXY08jmxSSgk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
    <script src="<?= $assetspath ?>style/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</head>
<div class="preloader">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Lade...</span>
    </div>
</div>
