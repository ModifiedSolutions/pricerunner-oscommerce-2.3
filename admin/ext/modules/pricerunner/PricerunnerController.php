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

use PricerunnerSDK\PricerunnerSDK;
use PricerunnerSDK\Models\Product;
use PricerunnerSDK\Errors\ProductErrorRenderer;
use CustomValidator\osCommerceProductCollectionValidator;

/**
 * Class PricerunnerController
 * @package osCommercePricerunnerFeed
 */

class PricerunnerController
{
    /**
     * This is our internal model for the controller
     * @var PricerunnerModel
     */
    protected $model;


    /**
     * This is our internal configurations for the add-on
     * We must make sure to update this every time we make changes
     * @var array
     */
    protected $config;


    /**
     * PricerunnerController constructor
     */
    public function __construct()
    {
        $this->model  = new PricerunnerModel();
        $this->config = $this->prepareConfigurations();
    }


    /**
     * Validates the provided $_GET hash if any
     * @return bool
     */
    public function validateHash()
    {
        $knownHash = $this->model->getLocalHash();
        if (($_GET['hash'] !== $knownHash)) {
            return false;
        }

        return true;
    }


    /**
     * First step of data processing before its sent to Pricerunner
     * @return array|Exception|null
     */
    public function handleRegistrationData()
    {
        $response = $this->validateRegistrationData();
        if ($response['valid'] !== true) {
            return $response;
        }

        $store  = STORE_NAME;
        $phone  = $_POST['phone'];
        $email  = $_POST['email'];
        $domain = ENABLE_SSL_CATALOG ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER;
        $url    = $domain . DIR_WS_ADMIN .'pricerunner_feed.php?hash='. $this->config['pricerunner_feed_hash'];

        $response = $this->model->activatePlugin($store, $phone, $email, $domain, $url);
        if (empty($response)) {

            $this->config = $this->prepareConfigurations();
            return [
                'message' => "Plugin has been successfully activated",
                'valid'   => true
            ];
        } else {

            return [
                'message' => "Plugin wasn't successfully activated (Error: " . $response->getMessage() . ")",
                'valid'   => false
            ];
        }
    }


    /**
     * Deletes our database entries and re-prepares our $config
     * @return array
     */
    public function handlePluginReset()
    {
        $this->model->resetPlugin();
        $this->config = $this->prepareConfigurations();

        return [
            'message' => "Plugin has been successfully reset",
            'valid'   => true
        ];
    }


    /**
     * This method prepares every product in the store as an XML string
     * @return string
     */
    public function displayFeed()
    {
        $products = $this->model->getAllProducts();
        $products = $this->instantiateAllProducts($products);
        $products = PricerunnerSDK::generateDataContainer($products, true, new osCommerceProductCollectionValidator());

        return $products->getXmlString();
    }


    /**
     * This method prepares a rendered view with every found error in the product feed
     * @return string
     */
    public function displayErrors()
    {
        $products = $this->model->getAllProducts();
        $products = $this->instantiateAllProducts($products);
        $products = PricerunnerSDK::validateProducts($products, new osCommerceProductCollectionValidator());
        $products = new ProductErrorRenderer($products);

        return $products->render();
    }


    /**
     * Method to return our $config in a public scope
     * We need this to present it in our view file
     * @return array
     */
    public function getConfigurations()
    {
        return $this->config;
    }


    /**
     * Prepares our $config as an array
     * @return array
     */
    private function prepareConfigurations()
    {
        $configResults = $this->model->getConfigurations();
        if (tep_db_num_rows($configResults) == 0) {
            $this->model->setupConfigurations();
            $configResults = $this->model->getConfigurations();
        }

        $config = [];
        while ($row = tep_db_fetch_array($configResults)) {
            $key = $row['configuration_key'];
            $config["$key"] = $row['configuration_value'];
        }

        return $config;
    }


    /**
     * Checks the data integrity before it's sent to Pricerunner
     * @return array
     */
    private function validateRegistrationData()
    {
        if (!isset($_POST['phone']) || !isset($_POST['email'])) {
            return [
                'message' => "Plugin wasn't successfully activated (Error: Form data not found )",
                'valid'   => false
            ];
        }

        if (strlen(preg_replace("/[^\d]/", '', $_POST['phone'])) < 8) {
            return [
                'message' => "Plugin wasn't successfully activated (Error: Phone number contains less than 8 digits )",
                'valid'   => false
            ];
        }

        if ($this->config['pricerunner_feed_activated']) {
            return [
                'message' => "Plugin wasn't successfully activated (Error: Plugin is already activated )",
                'valid'   => false
            ];
        }

        return [
            'message' => "",
            'valid'   => true
        ];
    }


    /**
     * Serializes every product in correspondence with our SDK
     * @param resource $products
     * @return array
     */
    private function instantiateAllProducts($products)
    {
        $shippingConfig = $this->model->getShippingConfig();
        $categoryConfig = $this->model->getCategoriesConfig();

        $instantiatedArray = [];
        while($productValues = tep_db_fetch_array($products)) {
            $instantiatedArray[] = $this->serializeSingleProduct(new Product(), $productValues, $categoryConfig, $shippingConfig);
        }

        return $instantiatedArray;
    }


    /**
     * Method to assign values to a new SDK product
     * @param Product $product
     * @param array $productValues
     * @param array $categories
     * @param array $shipping
     * @return Product
     */
    private function serializeSingleProduct(Product $product, $productValues, $categories, $shipping)
    {
        $product->setCategoryName($categories[$productValues['categories_id']]);
        $product->setProductName($productValues['products_name']);
        $product->setSku($productValues['sku']);

        if ($productValues['tax_rate'] > 0) {
            $taxRate = (floatval($productValues['tax_rate']) / 100) + 1;
            $productValues['real_price'] = $productValues['real_price'] * $taxRate;
        }

        $product->setPrice($productValues['real_price']);

        $product->setShippingCost('');
        if ($shipping['MODULE_SHIPPING_FLAT_STATUS'] == true) {
            $product->setShippingCost($shipping['MODULE_SHIPPING_FLAT_COST']);
        }

        $product->setProductUrl(HTTP_SERVER . DIR_WS_CATALOG . "product_info.php" . '?products_id=' . $productValues["products_id"]);
        $product->setManufacturer($productValues['manufacturers_name']);

        $productValues['products_description'] = PricerunnerSDK::getXmlReadyString($productValues['products_description']);
        $product->setDescription($productValues['products_description']);
        $product->setImageUrl((ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . DIR_WS_IMAGES . $productValues['image']);

        $product->setStockStatus('Out of Stock');
        if (!empty($productValues['stock'])) {
            $product->setStockStatus('In Stock');
        }

        return $product;
    }
}
