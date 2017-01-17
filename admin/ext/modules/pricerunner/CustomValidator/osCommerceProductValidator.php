<?php

namespace CustomValidator;

use PricerunnerSDK\Validators\ProductValidator;

class osCommerceProductValidator extends ProductValidator
{
    public function validate()
    {
        $this->validateCategoryName();
        $this->validateProductName();
        $this->validateSku();
        $this->validatePrice();
        $this->validateProductUrl();
        $this->validateManufacturer();
        $this->validateShippingCost();
        $this->validateUpc();
        $this->validateDescription();
        $this->validateStockStatus();
    }
}