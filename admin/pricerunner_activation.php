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

require_once(__DIR__ . '/ext/modules/pricerunner/PricerunnerFiles.php');
require_once(__DIR__ . '/includes/application_top.php');

require_once(DIR_WS_INCLUDES . 'template_top.php');

$controller = new PricerunnerController();

$action = '';
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}

if ($action == 'register') {
    $response = $controller->handleRegistrationData();
} elseif ($action == 'reset') {
    $response = $controller->handlePluginReset();
}

$config = $controller->getConfigurations();

$hash   = $config['pricerunner_feed_hash'];
$phone  = $config['pricerunner_feed_phone'];
$email  = $config['pricerunner_feed_email'];
$active = $config['pricerunner_feed_activated'];

$domain = ENABLE_SSL_CATALOG ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER;
$feed   = $domain . DIR_WS_ADMIN .'pricerunner_feed.php?hash='. $hash;

?>
    <style>
        .pricerunner-table {
            width: 60%;
            border-spacing: 5px;
        }

        .pricerunner-content {
            width: 30%;
            padding: 5px;
        }

        .pricerunner-input {
            width: 75%;
            float: right;
            display: table;
            height: 17px;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .pricerunner-input-label {
            font-weight: bold;
            color: #727272;
            margin-left: 30px;
        }

        .pricerunner-input-info {
            width: 75%;
            float: right;
            display: table;
        }

        .pricerunner-width-100 {
            width: 100px;
        }

        .pricerunner-width-150 {
            width: 150px;
        }

        .pricerunner-button {
            font-weight: bold;
            border-radius: 5px;
            border: 1px solid #c5dbec;
            background: #dfeffc 50% 50% repeat-x;
            color: #2e6e9e;
            padding: 8px;
        }

        .pricerunner-href {
            float: right;
            text-align: center;
            margin-right: 80px;
            margin-left: 20px;
        }

        .pricerunner-wrapper {
            padding: 10px;
            margin-left: 30px;
            border-radius: 10px;
            font-size: 1.2em;
        }

        .pricerunner-info {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }

        .pricerunner-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }

        .pricerunner-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        .pricerunner-readonly {
            background-color: #ebebe4;
            color: #545454;
        }

        .pricerunner-button:hover {
            border: 1px solid #79b7e7;
            background-color: rgba(98, 174, 232, 0.37);
            color: #2e6e9e;
        }

        .pricerunner-href:active,
        .pricerunner-href:visited,
        .pricerunner-href:link {
            text-decoration: none;
        }
    </style>

    <h1 class="pageHeading">Pricerunner XML Feed Add-On</h1>

    <table class="pricerunner-table">
        <tr>
            <td>
            <?php if($active): ?>
                <div class="pricerunner-wrapper pricerunner-info">
                    Thank you for your application.<br>
                    We've listed your registered information below.<br>
                </div>
            <?php else: ?>
                <div class="pricerunner-wrapper pricerunner-info">
                    This addon allows you to feed products to Pricerunner.<br>
                    In order to start using the addon you must first fill the application form.<br>
                    After filling out the application form you will be contacted by Pricerunner.<br>
                </div>
            <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>
            <?php if (isset($response) && $response['valid']): ?>
                <div class="pricerunner-wrapper pricerunner-success"><?php echo $response['message'] ?></div>
            <?php elseif (isset($response) && !$response['valid']): ?>
                <div class="pricerunner-wrapper pricerunner-error"><?php echo $response['message'] ?></div>
            <?php endif; ?>
            </td>
        </tr>
    </table>

    <br>

    <form method="post">
        <table class="pricerunner-table">
            <tr>
                <td class="pricerunner-content">
                    <label class="pricerunner-input-label" for="pricerunner_shop">Name</label>
                    <input class="pricerunner-input pricerunner-readonly" type="text" name="shop" id="pricerunner_shop"
                           value="<?php echo STORE_NAME ?>" readonly>
                    <br>
                    <div class="pricerunner-input-info">&#x25B4; Pricerunner will associate you with this shop name</div>
                </td>
            </tr>
            <tr>
                <td class="pricerunner-content">
                    <label class="pricerunner-input-label" for="pricerunner_domain">Domain</label>
                    <input class="pricerunner-input pricerunner-readonly" type="text" name="domain" id="pricerunner_domain"
                           value="<?php echo ENABLE_SSL_CATALOG ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER ?>" readonly>
                    <br>
                    <div class="pricerunner-input-info">&#x25B4; Pricerunner will associate you with this domain</div>
                </td>
            </tr>
            <tr>
                <td class="pricerunner-content">
                    <label class="pricerunner-input-label" for="pricerunner_email">Email</label>
                    <input class="pricerunner-input <?php echo (!$active) ?: "pricerunner-readonly" ?>" type="email" name="email" id="pricerunner_email"
                           value="<?php echo $email ?>" <?php echo (!$active) ?: "readonly" ?>>
                    <br>
                    <div class="pricerunner-input-info">&#x25B4; Pricerunner can contact you on this email</div>
                </td>
            </tr>
            <tr>
                <td class="pricerunner-content">
                    <label class="pricerunner-input-label" for="pricerunner_phone">Phone</label>
                    <input class="pricerunner-input <?php echo (!$active) ?: "pricerunner-readonly" ?>" type="text" name="phone" id="pricerunner_phone"
                           required pattern="^[-.()+\d ]{8,}" title="Phone numbers should contain at least 8 digits"
                           placeholder="Please provide a phone number that Pricerunner can use to contact you"
                           value="<?php echo $phone ?>" <?php echo (!$active) ?: "readonly" ?>>
                    <br>
                    <div class="pricerunner-input-info">&#x25B4; Pricerunner can contract you on this phone number</div>
                </td>
            </tr>
            <tr>
                <td class="pricerunner-content">
                    <label class="pricerunner-input-label" for="pricerunner_link">Link</label>
                    <input class="pricerunner-input pricerunner-readonly" type="text" name="link" id="pricerunner_link"
                           value="<?php echo $feed ?>" readonly>
                    <br>
                    <div class="pricerunner-input-info">&#x25B4; Pricerunner will receive this feed URL</div>
                </td>
            </tr>
            <tr>
                <td class="pricerunner-content">
                <?php if (!$active): ?>
                    <input type="hidden" name="action" value="register">
                    <input class="pricerunner-button pricerunner-width-100"
                           type="submit" value="Register">
                <?php elseif ($active): ?>
                    <input type="hidden" name="action" value="reset">
                    <input class="pricerunner-button pricerunner-width-100"
                           type="submit" value="Reset">
                <?php endif; ?>
                    <a href="<?php echo $feed . '&test=1' ?>" class="pricerunner-href" target="_blank">
                       <div class="pricerunner-button pricerunner-width-150">
                           Check for errors
                       </div>
                    </a>
                    <a href="<?php echo $feed ?>" class="pricerunner-href" target="_blank">
                        <div class="pricerunner-button pricerunner-width-150">
                            Check the feed
                        </div>
                    </a>
                </td>
            </tr>
        </table>
    </form>

<?php
require_once(DIR_WS_INCLUDES . 'template_bottom.php');
