<?php

namespace CustomValidator;

use PricerunnerSDK\Models\Product;
use PricerunnerSDK\Validators\ProductCollectionValidator;
use PricerunnerSDK\Validators\ProductValidator;

class osCommerceProductCollectionValidator extends ProductCollectionValidator
{
    protected function createProductValidator($product)
    {
        return new osCommerceProductValidator($product);
    }

    protected function validateProductAgainstProductCollection(Product $product, ProductValidator $productValidator)
    {
        $this->validateEan($product, $productValidator);
        $this->validateSku($product, $productValidator);
    }
}