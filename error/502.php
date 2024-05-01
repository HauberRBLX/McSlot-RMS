<?php include '../settings/config.php' ?>
<?php include '../settings/head_admin.php' ?>

<body>
    <a href="<?= $siteurl ?>" class="btn btn-neutral btn-icon-only rounded-circle position-absolute left-4 top-4 d-none d-lg-inline-flex" data-toggle="tooltip" data-placement="right" title="Zurück">
        <span class="btn-inner--icon">
            <i class="fa-solid fa-arrow-left"></i>
        </span>
    </a>
    <section>
        <div class="container d-flex flex-column">
            <div class="row align-items-center justify-content-between min-vh-100">
                <div class="col-12 col-md-6 col-xl-5 order-md-1 text-center text-md-left">
                    <h6 class="display-1 mb-3 font-weight-600 text-warning">502</h6>
                    <p class="lead text-lg mb-5">Ungültiges Gateway. Der Server, als Gateway oder Proxy fungierend, hat eine ungültige Antwort von einem Upstream-Server erhalten.</p>
                    <a href="<?= $siteurl ?>" class="btn btn-dark btn-icon hover-translate-y-n3">
                        <span class="btn-inner--icon">
                            <i data-feather="home"></i>
                        </span>
                        <span class="btn-inner--text">Zur Startseite</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>
