<?php declare(strict_types=1);

namespace VitesseCms\Export\Services;

use ChannelEngine\Merchant\ApiClient\Api\ProductApi;
use ChannelEngine\Merchant\ApiClient\ApiException;
use ChannelEngine\Merchant\ApiClient\Configuration;
use ChannelEngine\Merchant\ApiClient\Model\CollectionOfMerchantProductResponse;
use ChannelEngine\Merchant\ApiClient\Model\SingleOfMerchantProductResponse;
use VitesseCms\Content\Models\Item;
use VitesseCms\Export\Factories\ChannelEngineFactory;

class ChannelEngineService
{
    protected $apiConfig;

    protected $productApi;

    public function __construct()
    {
        $this->apiConfig = Configuration::getDefaultConfiguration();
        $this->apiConfig->setHost('');
        $this->apiConfig->setApiKey('apikey', '');

        $this->productApi = new ProductApi(null, $this->apiConfig);
    }

    public function getProduct(string $id): ?SingleOfMerchantProductResponse
    {
        try {
            return $this->productApi->productGetByMerchantProductNo($id);
        } catch (ApiException $e) {
            return null;
        }
    }

    public function getProducts(): ?CollectionOfMerchantProductResponse
    {
        try {
            return $this->productApi->productGetByFilter(null, null);
        } catch (ApiException $e) {
            return null;
        }
    }

    public function createOrUpdateProduct(Item $item): bool
    {
        try {
            $result = $this->productApi->productCreate(ChannelEngineFactory::createByItem($item));
            return $result['success'];
        } catch (ApiException $e) {
            return false;
        }
    }

    public function deleteProduct(string $id): bool
    {
        try {
            $result = $this->productApi->productDelete($id);
            return $result['success'];
        } catch (ApiException $e) {
            return false;
        }
    }
}
