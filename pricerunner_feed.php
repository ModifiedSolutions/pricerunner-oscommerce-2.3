<?php

    /**
     * 2016 Modified Solutions ApS www.modified.dk hej@modified.dk
     *
     * NOTICE OF LICENSE
     *
     * This Source Code Form is subject to the terms of the Mozilla Public
     * License, v. 2.0. If a copy of the MPL was not distributed with this
     * file, You can obtain one at http://mozilla.org/MPL/2.0/.
     **/

    define('PRICRUNNER_OFFICIAL_PLUGIN_VERSION', 'oscommerce-2.3-v1.0.4');

    error_reporting(E_ALL);
    ini_set('display_errors', true);

    require_once(__DIR__ . '/admin/ext/modules/pricerunner/PricerunnerFiles.php');
    require_once(__DIR__ . '/includes/application_top.php');

    $controller = new PricerunnerController();

    if ($controller->validateHash() !== true) {
        die('Invalid hash');
    }

    if (isset($_GET['test'])) {
        echo $controller->displayErrors();
        exit;
    }

    if (!headers_sent()) {
        header("Content-Type:application/xml; charset=utf-8");
    }

    echo $controller->displayFeed();

