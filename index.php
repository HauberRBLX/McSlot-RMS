<?php include 'settings/config.php' ?>
<?php include 'settings/head_index.php' ?>
<?php include 'settings/header.php' ?>
<title>
    <?= str_replace('{websiteName}', $name, $translations['index_page']['title']) ?>
</title>


<body>
    <section class="slice pt-md-5 pb-md-0 bg-section-secondary">
        <div class="bg-absolute-cover bg-size--contain d-flex align-items-center">
        <figure class="w-100 d-none d-lg-block">
            <img src="<?= $assetspath ?>style/img/svg/backgrounds/dot-map.svg" class="svg-inject"
                alt="Image placeholder" style="pointer-events: none; user-select: none;" />
        </figure>

        </div>

        <div class="container position-relative zindex-100 pt-lg-6">

            <div class="row align-items-center">

                <div class="col-12 col-md-6 order-md-2 mb-5 mb-md-0 d-none d-md-block">

                    <div class="position-relative left-5 left-md-0">

                        <figure>
                            <img alt="Image placeholder" src="<?= $assetspath ?>style/img/index-welcome.png"
                                class="img-fluid mw-md-130 mw-lg-100 rounded perspective-md-right" style="pointer-events: none; user-select: none;">
                        </figure>
                    </div>
                    <div
                        class="card shadow-lg mb-3 col-8 col-md-6 col-lg-5 px-0 position-absolute bottom-n6 bottom-md-n5 left-4 left-md-n4 z-index-100">
                        <div class="card-body px-lg-4 pt-4 text-center h-100">
                            <div class="icon icon-lg icon-shape rounded-circle bg-soft-info text-info">
                                <svg class="svg-inline--fa fa-rotate" aria-hidden="true" focusable="false"
                                    data-prefix="fas" data-icon="rotate" role="img" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512" data-fa-i2svg="">
                                    <path fill="currentColor"
                                        d="M449.9 39.96l-48.5 48.53C362.5 53.19 311.4 32 256 32C161.5 32 78.59 92.34 49.58 182.2c-5.438 16.81 3.797 34.88 20.61 40.28c16.97 5.5 34.86-3.812 40.3-20.59C130.9 138.5 189.4 96 256 96c37.96 0 73 14.18 100.2 37.8L311.1 178C295.1 194.8 306.8 223.4 330.4 224h146.9C487.7 223.7 496 215.3 496 204.9V59.04C496 34.99 466.9 22.95 449.9 39.96zM441.8 289.6c-16.94-5.438-34.88 3.812-40.3 20.59C381.1 373.5 322.6 416 256 416c-37.96 0-73-14.18-100.2-37.8L200 334C216.9 317.2 205.2 288.6 181.6 288H34.66C24.32 288.3 16 296.7 16 307.1v145.9c0 24.04 29.07 36.08 46.07 19.07l48.5-48.53C149.5 458.8 200.6 480 255.1 480c94.45 0 177.4-60.34 206.4-150.2C467.9 313 458.6 294.1 441.8 289.6z">
                                    </path>
                                </svg>
                                <!-- <i class="fa-solid fa-rotate"></i> -->
                            </div>

                            <p class="mt-4 text-muted mb-0">
                                <?= str_replace('{websiteName}', $name, $translations['index_page']['alert']['newVersion']['content']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 order-md-1 pr-lg-5 mt-5 mt-md-n7">
                    <?php
                    $query = "SELECT setting_value FROM settings WHERE setting_name = 'maintenance'";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        // Datenbankergebnisse abrufen
                        $row = $result->fetch_assoc();
                        $maintenanceMode = $row["setting_value"];

                        // Überprüfen, ob der Wartungsmodus aktiviert ist
                        if ($maintenanceMode == 1) {
                            // Wartungsmodus ist aktiv, also die Meldung anzeigen
                            echo '<a href="' . $status_url . '">
                <div class="alert alert-modern alert-dark">
                    <span class="badge badge-danger badge-pill">
                    ' . str_replace('{websiteName}', $name, $translations['index_page']['alert']['maintenance']['badge']) . '
                    </span>
                    <span class="alert-content">' . str_replace('{websiteName}', $name, $translations['index_page']['alert']['maintenance']['content']) . '</span>
                </div>
              </a>';
                        }
                    }


                    ?>
<a href="closed">
                <div class="alert alert-modern alert-dark">
                    <span class="badge badge-danger badge-pill">
                    WICHTIGE INFORMATION
                    </span>
                    <span class="alert-content">McSlot Schließt - Weitere Informationen</span>
                </div>
              </a>
                    <h1 class="display-4 font-weight-bolder mb-3">
                        <?= str_replace('{websiteName}', '<strong class="text-primary">' . $name . '</strong>', $translations['index_page']['miscellaneous']['timeToSwitch']) ?>
                    </h1>
                    <p class="lead text-muted">
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['miscellaneous']['yourSystems']) ?>
                    </p>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        ?>
                        <div class="mt-5">
                            <a href="<?= ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url) ?>"
                                class="btn btn-animated btn-primary btn-animated-x">
                                <span class="btn-inner--visible">
                                    <?= str_replace('{websiteName}', $name, $translations['index_page']['button']['toDashboard']) ?>
                                </span>
                                <span class="btn-inner--hidden">
                                    <svg class="svg-inline--fa fa-arrow-right" aria-hidden="true" focusable="false"
                                        data-prefix="fas" data-icon="arrow-right" role="img"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="">
                                        <path fill="currentColor"
                                            d="M438.6 278.6l-160 160C272.4 444.9 264.2 448 256 448s-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L338.8 288H32C14.33 288 .0016 273.7 .0016 256S14.33 224 32 224h306.8l-105.4-105.4c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l160 160C451.1 245.9 451.1 266.1 438.6 278.6z">
                                        </path>
                                    </svg>
                                </span>
                            </a>
                        </div>
                        <?php
                    } else {
                        // Benutzer ist nicht angemeldet, zeige den Registrierungslink an
                        ?>
                        <div class="mt-5">
                            <a href="<?= ($phpenable === 'true' ? $siteurl . $register_url . '.php' : $siteurl . $register_url) ?>"
                                class="btn btn-animated btn-primary btn-animated-x">
                                <span class="btn-inner--visible">
                                    <?= str_replace('{websiteName}', $name, $translations['index_page']['button']['registerNow']) ?>
                                </span>
                                <span class="btn-inner--hidden">
                                    <svg class="svg-inline--fa fa-arrow-right" aria-hidden="true" focusable="false"
                                        data-prefix="fas" data-icon="arrow-right" role="img"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="">
                                        <path fill="currentColor"
                                            d="M438.6 278.6l-160 160C272.4 444.9 264.2 448 256 448s-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L338.8 288H32C14.33 288 .0016 273.7 .0016 256S14.33 224 32 224h306.8l-105.4-105.4c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l160 160C451.1 245.9 451.1 266.1 438.6 278.6z">
                                        </path>
                                    </svg>
                                </span>
                            </a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="shape-container shape-line shape-position-bottom">
            <svg width="2560px" height="100px" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none" x="0px" y="0px"
                viewBox="0 0 2560 100" style="enable-background:new 0 0 2560 100;" xml:space="preserve" class="">
                <polygon points="2560 0 2560 100 0 100"></polygon>
            </svg>
        </div>
    </section>
    <section class="slice slice-lg pt-md-9 delimiter-bottom">
        <div class="container">
            <div class="row row-grid">
                <div class="col-md-4">
                    <div class="pb-4">
                        <div class="icon">
                            <span class="fa-layers fa-fw">
                                <svg class="svg-inline--fa fa-circle text-blue" aria-hidden="true" focusable="false"
                                    data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512" data-fa-i2svg="">
                                    <path fill="currentColor"
                                        d="M512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256z">
                                    </path>
                                </svg>
                                <!-- <i class="fa-solid fa-circle text-blue"></i> -->
                                <svg class="svg-inline--fa fa-truck-fast text-white" data-fa-transform="shrink-6"
                                    aria-hidden="true" focusable="false" data-prefix="fas" data-icon="truck-fast"
                                    role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""
                                    style="transform-origin: 0.625em 0.5em;">
                                    <g transform="translate(320 256)">
                                        <g transform="translate(0, 0)  scale(0.625, 0.625)  rotate(0 0 0)">
                                            <path fill="currentColor"
                                                d="M112 0C85.49 0 64 21.49 64 48V96H16C7.163 96 0 103.2 0 112C0 120.8 7.163 128 16 128H272C280.8 128 288 135.2 288 144C288 152.8 280.8 160 272 160H48C39.16 160 32 167.2 32 176C32 184.8 39.16 192 48 192H240C248.8 192 256 199.2 256 208C256 216.8 248.8 224 240 224H16C7.163 224 0 231.2 0 240C0 248.8 7.163 256 16 256H208C216.8 256 224 263.2 224 272C224 280.8 216.8 288 208 288H64V416C64 469 106.1 512 160 512C213 512 256 469 256 416H384C384 469 426.1 512 480 512C533 512 576 469 576 416H608C625.7 416 640 401.7 640 384C640 366.3 625.7 352 608 352V237.3C608 220.3 601.3 204 589.3 192L512 114.7C499.1 102.7 483.7 96 466.7 96H416V48C416 21.49 394.5 0 368 0H112zM544 237.3V256H416V160H466.7L544 237.3zM160 464C133.5 464 112 442.5 112 416C112 389.5 133.5 368 160 368C186.5 368 208 389.5 208 416C208 442.5 186.5 464 160 464zM528 416C528 442.5 506.5 464 480 464C453.5 464 432 442.5 432 416C432 389.5 453.5 368 480 368C506.5 368 528 389.5 528 416z"
                                                transform="translate(-320 -256)"></path>
                                        </g>
                                    </g>
                                </svg>
                                <!-- <i class="fa-solid fa-truck-fast text-white" data-fa-transform="shrink-6"></i> -->
                            </span>
                        </div>
                    </div>
                    <h5>
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['section']['downloads']['heading']) ?>
                    </h5>
                    <p class="text-muted mb-0">
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['section']['downloads']['content']) ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="pb-4">
                        <div class="icon">
                            <span class="fa-layers fa-fw">
                                <svg class="svg-inline--fa fa-circle text-blue" aria-hidden="true" focusable="false"
                                    data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512" data-fa-i2svg="">
                                    <path fill="currentColor"
                                        d="M512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256z">
                                    </path>
                                </svg>
                                <!-- <i class="fa-solid fa-circle text-blue"></i> -->
                                <svg class="svg-inline--fa fa-question text-white" data-fa-transform="shrink-6"
                                    aria-hidden="true" focusable="false" data-prefix="fas" data-icon="question"
                                    role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" data-fa-i2svg=""
                                    style="transform-origin: 0.3125em 0.5em;">
                                    <g transform="translate(160 256)">
                                        <g transform="translate(0, 0)  scale(0.625, 0.625)  rotate(0 0 0)">
                                            <path fill="currentColor"
                                                d="M204.3 32.01H96c-52.94 0-96 43.06-96 96c0 17.67 14.31 31.1 32 31.1s32-14.32 32-31.1c0-17.64 14.34-32 32-32h108.3C232.8 96.01 256 119.2 256 147.8c0 19.72-10.97 37.47-30.5 47.33L127.8 252.4C117.1 258.2 112 268.7 112 280v40c0 17.67 14.31 31.99 32 31.99s32-14.32 32-31.99V298.3L256 251.3c39.47-19.75 64-59.42 64-103.5C320 83.95 268.1 32.01 204.3 32.01zM144 400c-22.09 0-40 17.91-40 40s17.91 39.1 40 39.1s40-17.9 40-39.1S166.1 400 144 400z"
                                                transform="translate(-160 -256)"></path>
                                        </g>
                                    </g>
                                </svg>
                                <!-- <i class="fa-solid fa-question text-white" data-fa-transform="shrink-6"></i> -->
                            </span>
                        </div>
                    </div>
                    <h5>
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['section']['support']['heading']) ?>
                    </h5>
                    <p class="text-muted mb-0">
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['section']['support']['content']) ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="pb-4">
                        <div class="icon">
                            <span class="fa-layers fa-fw">
                                <svg class="svg-inline--fa fa-circle text-blue" aria-hidden="true" focusable="false"
                                    data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512" data-fa-i2svg="">
                                    <path fill="currentColor"
                                        d="M512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256z">
                                    </path>
                                </svg>
                                <!-- <i class="fa-solid fa-circle text-blue"></i> -->
                                <svg class="svg-inline--fa fa-server text-white" data-fa-transform="shrink-6"
                                    aria-hidden="true" focusable="false" data-prefix="fas" data-icon="server" role="img"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""
                                    style="transform-origin: 0.5em 0.5em;">
                                    <g transform="translate(256 256)">
                                        <g transform="translate(0, 0)  scale(0.625, 0.625)  rotate(0 0 0)">
                                            <path fill="currentColor"
                                                d="M480 288H32c-17.62 0-32 14.38-32 32v128c0 17.62 14.38 32 32 32h448c17.62 0 32-14.38 32-32v-128C512 302.4 497.6 288 480 288zM352 408c-13.25 0-24-10.75-24-24s10.75-24 24-24s24 10.75 24 24S365.3 408 352 408zM416 408c-13.25 0-24-10.75-24-24s10.75-24 24-24s24 10.75 24 24S429.3 408 416 408zM480 32H32C14.38 32 0 46.38 0 64v128c0 17.62 14.38 32 32 32h448c17.62 0 32-14.38 32-32V64C512 46.38 497.6 32 480 32zM352 152c-13.25 0-24-10.75-24-24S338.8 104 352 104S376 114.8 376 128S365.3 152 352 152zM416 152c-13.25 0-24-10.75-24-24S402.8 104 416 104S440 114.8 440 128S429.3 152 416 152z"
                                                transform="translate(-256 -256)"></path>
                                        </g>
                                    </g>
                                </svg>
                                <!-- <i class="fa-solid fa-server text-white" data-fa-transform="shrink-6"></i> -->
                            </span>
                        </div>
                    </div>
                    <h5>
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['section']['servers']['heading']) ?>
                    </h5>
                    <p class="text-muted mb-0">
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['section']['servers']['content']) ?>
                    </p>
                </div>
            </div>
        </div>
    </section>
 
    <section class="slice slice-lg">
        <div class="container">
            <div class="row mb-5 justify-content-center text-center">
                <div class="col-lg-9 col-md-10">
                    <h2 class="mt-4">
                        <?= str_replace('{websiteName}', $name, $translations['index_page']['ourSystems']) ?>
                    </h2>
                    <div class="mt-2">
                        <p class="text-muted mb-0">
                            <?= str_replace('{websiteName}', '<strong>' . $name . '</strong>', $translations['index_page']['aboutUs']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8"><img alt="Image placeholder"
                        src="<?= $assetspath ?>style/img/svg/illustrations/illustration-4.svg" class="img-fluid" style="pointer-events: none; user-select: none;">
                </div>
            </div>
        </div>
    </section>
    <?php include 'settings/footer.php' ?>
</body>

</html>