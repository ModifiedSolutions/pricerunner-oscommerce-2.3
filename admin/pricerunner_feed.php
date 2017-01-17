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

require_once(__DIR__ . '/ext/modules/pricerunner/PricerunnerFiles.php');
require_once(__DIR__ . '/includes/application_top.php');

$controller = new PricerunnerController();

if ($controller->validateHash() !== true) {
    exit;
}

if (isset($_GET['test']) && $_GET['test'] == 1) {
    echo $controller->displayErrors();
    exit;
}

header("Content-Type:application/xml; charset=utf-8");
echo $controller->displayFeed();
