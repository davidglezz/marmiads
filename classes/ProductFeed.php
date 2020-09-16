<?php

/**
 * This class allows get the response data required by the endpoint /feed/products
 */
class ProductFeed
{
    private $idLang;
    private $link;

    public function __construct()
    {
        $this->idLang = Configuration::get('PS_LANG_DEFAULT');
        $this->link = Context::getContext()->link;
    }

    /**
     * Main method to get /feed/products endpoint response data
     *
     * @param int $store aka. id_shop
     * @param int $start row or offset
     * @param int|string $rows nº of records to return
     * @param boolean $includeVariations
     * @return array Products feed
     */
    public function get($store = null, $start = 0, $rows = 'all', $includeVariations = false)
    {
        if (!$store) {
            $store = Configuration::get('PS_SHOP_DEFAULT');
        }

        Shop::setContext(Shop::CONTEXT_SHOP, $store);

        $products = Product::getProducts($this->idLang, $start, (int) $rows, 'id_product', 'ASC', false, true);
        return array_map(function ($productDetails) use ($includeVariations) {
            $product = new Product($productDetails['id_product']);
            $result = [
                'id' => $product->id,
                'name' => $productDetails['name'],
                'link' => $this->link->getProductLink($product),
                'image' => $this->getProductImages($product->id),
                'availability' => $this->getProductAvailability($product->id),
                'price' => $this->getProductPrice($product),
            ];

            if ($includeVariations) {
                $variations = $this->getProductVariants($product);
                if (!empty($variations)) {
                    $result['variations'] = $variations;
                }
            }

            return $result;
        }, $products);
    }

    /**
     * Get an array of images of a product.
     *
     * @param int $idProduct
     *
     * @return array Images with url and alt keys
     */
    private function getProductImages($idProduct)
    {
        return array_map(function ($image) {
            return [
                'url' => $this->link->getImageLink($image['legend'], $image['id_image']),
                'alt' => $image['legend'],
            ];
        }, Image::getImages($this->idLang, $idProduct));
    }

    /**
     * Get the availability of a product
     *
     * @param int $idProduct
     *
     * @return array Availability with stock key
     */
    private function getProductAvailability($idProduct)
    {
        return [
            'stock' => Product::getQuantity($idProduct),
        ];
    }

    /**
     * Get a product price
     *
     * @param Product $product
     *
     * @return array Price with regular and on_sale (if there is) keys
     */
    private function getProductPrice($product)
    {
        $price = [
            'regular' => $product->price
        ];

        $onSalePrice = Product::getPriceStatic($product->id, false);
        if ($onSalePrice != $product->price) {
            $price['on_sale'] = $onSalePrice;
        }

        return $price;
    }

    /**
     * Get an array of currencies.
     *
     * @param int $idShop
     *
     * @return array Currencies
     */
    private function getProductVariants($product)
    {
        $variants = $product->getAttributeCombinations($this->idLang);

        if (empty($variants)) {
            return false;
        }

        $result = [];
        foreach ($variants as $variant) {
            $variantId = $variant['id_product_attribute'];
            if (empty($result[$variantId])) {
                $result[$variantId] = [
                    'id' => $variantId,
                    'link' => $this->link->getProductLink($product, null, null, null, null, null, $variantId),
                ];
            }
            if ($variant['is_color_group']) {
                $attribute = new Attribute($variant['id_attribute']);
                $result[$variantId][$variant['group_name']] = [
                    'name' => $variant['attribute_name'],
                    'hex' => $attribute->color,
                ];
            } else {
                $result[$variantId][$variant['group_name']] = $variant['attribute_name'];
            }
        }

        return array_values($result);
    }
}
