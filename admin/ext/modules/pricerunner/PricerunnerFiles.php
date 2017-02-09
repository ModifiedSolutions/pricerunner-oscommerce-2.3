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

/**
 * Root directory of the Pricerunner plugin folder
 */

define('PRICERUNNER_PLUGIN_ROOT_DIR', dirname(__FILE__));

require_once(PRICERUNNER_PLUGIN_ROOT_DIR . '/PricerunnerController.php');
require_once(PRICERUNNER_PLUGIN_ROOT_DIR . '/PricerunnerModel.php');
require_once(PRICERUNNER_PLUGIN_ROOT_DIR . '/pricerunner-php-sdk/src/files.php');
require_once(PRICERUNNER_PLUGIN_ROOT_DIR . '/CustomValidator/osCommerceProductValidator.php');
require_once(PRICERUNNER_PLUGIN_ROOT_DIR . '/CustomValidator/osCommerceProductCollectionValidator.php');
