<?php include '../settings/config.php' ?>
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
   <link rel="manifest" href="manifest.json">
   <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/libs/swiper/dist/css/swiper.min.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/libs/@fancyapps/fancybox/dist/jquery.fancybox.min.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/css/preloader-dark.css" id="stylesheet">
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


<title>Changelogs - McSlot</title>
<body>
    <a href="<?= $siteurl ?>"class="btn btn-neutral btn-icon-only rounded-circle position-absolute left-4 top-4 d-none d-lg-inline-flex"
        data-toggle=tooltip data-placement=right title="Go back">
        <span class=btn-inner--icon>
            <i class="fa-solid fa-arrow-left"></i>
        </span>
    </a>
    <section>
        <div class="container d-flex flex-column">
            <div class="row align-items-center justify-content-between min-vh-100">
                <div class="col-12 col-md-6 col-xl-5 order-md-1 text-center text-md-left">
                    <h6 class="display-1 mb-3 font-weight-600 text-success">Changelogs</h6>
                    <a href="els" class="btn btn-dark btn-icon hover-translate-y-n3">
                        <span class=btn-inner--icon>
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <span class=btn-inner--text>ELS (EinkaufslisteSystem)</span>
                    </a><br><br>
                                      
                  <a href="urls" class="btn btn-dark btn-icon hover-translate-y-n3">
                        <span class=btn-inner--icon>
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <span class=btn-inner--text>URLS (URLShortener)</span>
                    </a>
                    <br><br>
                    <a href="<?php echo $siteurl ?>" class="btn btn-success btn-icon hover-translate-y-n3">
                        <span class=btn-inner--icon>
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <span class=btn-inner--text>Zur Startseite</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript">

        var message = "Sorry, right-click has been disabled";
        function clickIE() { if (document.all) { (message); return false; } }
        function clickNS(e) {
            if
                (document.layers || (document.getElementById && !document.all)) {
                if (e.which == 2 || e.which == 3) { (message); return false; }
            }
        }
        if (document.layers) { document.captureEvents(Event.MOUSEDOWN); document.onmousedown = clickNS; }
        else { document.onmouseup = clickNS; document.oncontextmenu = clickIE; }
        document.oncontextmenu = new Function("return false") 
    </script>