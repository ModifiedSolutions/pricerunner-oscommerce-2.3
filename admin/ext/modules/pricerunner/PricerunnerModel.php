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

/**
 * Class PricerunnerModel
 * @package osCommercePricerunnerFeed
 */

class PricerunnerModel
{
    /**
     * Database call for our generated hash
     * @return string
     */
    public function getLocalHash()
    {
        $hashQuery = "SELECT * FROM " . TABLE_CONFIGURATION . ' cfg ' .
            "WHERE cfg.configuration_key LIKE 'pricerunner_feed_hash'
            LIMIT 1";
        $knownHash = tep_db_query($hashQuery);
        $knownHash = tep_db_fetch_array($knownHash);

        return $knownHash['configuration_value'];
    }


    /**
     * Database call for the default shipping configurations
     * @return array
     */
    public function getShippingConfig()
    {
        $sql = "SELECT * FROM " . TABLE_CONFIGURATION . ' cfg ' .
               "WHERE cfg.configuration_key = 'MODULE_SHIPPING_FLAT_STATUS'
                OR cfg.configuration_key = 'MODULE_SHIPPING_FLAT_COST'
                LIMIT 2";
        $shipping = tep_db_query($sql);

        $result = [];
        foreach ($shipping as $value) {
            $key = $value['configuration_key'];
            $result[$key] = $value['configuration_value'];
        }

        return $result;
    }


    /**
     * Database call for the parent/child category relationships
     * @return array
     */
    public function getCategoriesConfig()
    {
        $query = "
            SELECT
                cat.categories_id,
                cat.parent_id,
                cd.categories_name
            FROM categories cat
            LEFT JOIN categories_description AS cd ON cat.categories_id
            WHERE cat.categories_id = cd.categories_id
            ORDER BY categories_id ASC";
        $resource = tep_db_query($query);

        $categories = [];
        while ($value = tep_db_fetch_array($resource)) {
            $key = $value['categories_id'];
            $categories[$key] = [
                'parent_id'     => $value['parent_id'],
                'category_id'   => $value['categories_id'],
                'category_name' => $value['categories_name']
            ];
        }

        return $this->sortCategories($categories);
    }


    /**
     * Database call for our own configurations
     * @return resource
     */
    public function getConfigurations()
    {
        $configQuery = "SELECT * FROM " . TABLE_CONFIGURATION . ' cfg ' .
            "WHERE cfg.configuration_key LIKE 'pricerunner_feed_%'";
        return tep_db_query($configQuery);
    }


    /**
     * Database call for every single product in a shop
     * Variant compatibility has been removed until further debugging as been conducted
     * The more variant aware query selects SKU like this:
     * "COALESCE(CONCAT(p.products_id, '-', pa.options_values_id), p.products_id) AS sku,"
     * @return resource
     */
    public function getAllProducts()
    {
        $query = "
            SELECT 
                p.products_id AS sku, 
                (CASE WHEN p.products_quantity <> 0 THEN TRUE ELSE FALSE END) AS stock,
                pd.products_name, pov.products_options_values_name, cd.categories_name, pd.products_description,
                m.manufacturers_name, p.products_id, pa.options_values_id, tr.tax_rate, pa.options_id, cd.categories_id,
                COALESCE(
                    (CASE WHEN pa.price_prefix = '+'
                          THEN p.products_price + pa.options_values_price
                          ELSE p.products_price - pa.options_values_price
                    END),
                    s.specials_new_products_price,
                    p.products_price
                ) AS real_price,
                COALESCE(pi.image, p.products_image) AS image
            FROM products AS p
            
            LEFT JOIN products_attributes AS pa ON pa.products_id = p.products_id
            LEFT JOIN products_options_values AS pov ON pa.options_values_id = pov.products_options_values_id
            LEFT JOIN products_description AS pd ON pd.products_id = p.products_id
            LEFT JOIN products_to_categories AS ptc ON ptc.products_id = p.products_id
            LEFT JOIN products_options_values_to_products_options AS povtpo ON povtpo.products_options_id = pa.options_id
                      AND povtpo.products_options_values_id = pov.products_options_values_id
            LEFT JOIN categories_description AS cd ON cd.categories_id = ptc.categories_id
            LEFT JOIN products_images AS pi ON pi.products_id = p.products_id
            LEFT JOIN manufacturers AS m ON m.manufacturers_id = p.manufacturers_id
            LEFT JOIN specials AS s ON s.products_id = p.products_id
            LEFT JOIN tax_rates AS tr ON tr.tax_class_id = p.products_tax_class_id
            
            GROUP BY sku
            ORDER BY p.products_id ASC, pa.products_attributes_id ASC";
        return tep_db_query($query);
    }


    /**
     * Returns the exception if sending contact information to Pricerunner somehow failed
     * Otherwise returns null
     * @param string $store
     * @param string $phone
     * @param string $email
     * @param string $domain
     * @param string $url
     * @return null|Exception
     */
    public function activatePlugin($store, $phone, $email, $domain, $url)
    {
        $response = $this->registration($store, $phone, $email, $domain, $url);
        if (!empty($response)) {
            return $response;
        }

        $this->setPluginActivation($phone, $email);
        return $response;
    }


    /**
     * Database call to delete our own configurations
     * @return void
     */
    public function resetPlugin()
    {
        $deleteQuery = "DELETE FROM " . TABLE_CONFIGURATION . ' ' .
            "WHERE configuration_key LIKE " . "'" . tep_db_input('pricerunner_feed_%') . "'";
        tep_db_query($deleteQuery);
    }


    /**
     * Database call to insert our own default configurations
     * @return void
     */
    public function setupConfigurations()
    {
        echo 'SETUP!!!!';
        $entries = [
            'activated'  => 0,
            'phone'      => '',
            'email'      => STORE_OWNER_EMAIL_ADDRESS,
            'hash'       => PricerunnerSDK::getRandomString()
        ];

        foreach ($entries as $key => $value) {
            $insertQuery = "INSERT INTO " . TABLE_CONFIGURATION . ' ' .
                "(configuration_key, configuration_value, configuration_group_id, date_added) " .
                "VALUES ("."'".tep_db_input('pricerunner_feed_'.$key)."'" .
                ', '. "'" . tep_db_input($value). "'" . ', ' . "'6', NOW())";
            tep_db_query($insertQuery);
        }
    }


    /**
     * Sorts our categories into an array with strings such as "Software > Games > RPG"
     * @param array $categories
     * @return array
     */
    private function sortCategories($categories)
    {
        $sortedCategories = [];
        foreach ($categories as $id => $category) {

            if ($category['parent_id'] == 0) {
                $sortedCategories[$id] = $category['category_name'];
            } else {
                $sortedCategories[$id] = $this->recursiveSortCategories(
                    $categories,
                    $category['parent_id'],
                    $category['category_id'],
                    $category['category_name']);
            }
        }

        return $sortedCategories;
    }


    /**
     * Appends the category names of all parents in a category child's name
     * @param array $allCategories
     * @param int $parentId
     * @param int $categoryId
     * @param string $categoryName
     * @return array
     */
    private function recursiveSortCategories($allCategories, $parentId, $categoryId, $categoryName)
    {
        foreach ($allCategories as $id => $category) {

            if ($id == $parentId) {

                $parentId     = $category['parent_id'];
                $categoryId   = $category['category_id'];
                $categoryName = ($categoryName !== $category['category_name'])
                                 ? $category['category_name'] . ' > ' . $categoryName : $categoryName;

                unset($allCategories[$id]);

                return $this->recursiveSortCategories(
                    $allCategories,
                    $parentId,
                    $categoryId,
                    $categoryName
                );
            }

            if ($id = $categoryId && $parentId == 0) {
                return $categoryName;
            }
        }

        return $categoryName;
    }


    /**
     * Database call to update our own configurations and flag it as activated
     * @param string $phone
     * @param string $email
     */
    private function setPluginActivation($phone, $email)
    {
        $entries = [
            'phone'     => $phone,
            'email'     => $email,
            'activated' => 1
        ];

        foreach ($entries as $key => $value) {
            $query = "UPDATE " . TABLE_CONFIGURATION . ' cfg ' .
                "SET cfg.configuration_value = " . "'" . tep_db_input($value) . "'" . ' ' .
                "WHERE cfg.configuration_key LIKE " . "'" . tep_db_input('pricerunner_feed_'.$key) . "'";
            tep_db_query($query);
        }
    }


    /**
     * Sends our post request to Pricerunner
     * @param string $store
     * @param string $phone
     * @param string $email
     * @param string $domain
     * @param string $url
     * @return Exception|null
     */
    private function registration($store, $phone, $email, $domain, $url)
    {
        try {
            PricerunnerSDK::postRegistration($store, $phone, $email, $domain, $url);
        } catch (Exception $exception) {

            return $exception;
        }

        return null;
    }
}
