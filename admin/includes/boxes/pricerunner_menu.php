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

$cl_box_groups[] = array(
    'heading' => 'Pricerunner',
    'apps'    => array(array(
        'code'  => 'pricerunner_activation.php',
        'title' => 'Activation',
        'link'  => tep_href_link('pricerunner_activation.php', "", (ENABLE_SSL_CATALOG == 'true' ? "SSL" : "NONSSL"))
    ))
);
