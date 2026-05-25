<?php
/**
 * UltraDev Search Helper
 *
 * @category    UltraDev
 * @package     UltraDev_Search
 * @version     1.0.0
 */
class UltraDev_Search_Helper_Data extends Mage_Core_Helper_Abstract
{
    // ── General ──────────────────────────────────────────────────────────

    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('ultradev_search/general/enable', $store);
    }

    public function getLimit($store = null)
    {
        return (int) Mage::getStoreConfig('ultradev_search/general/limit', $store);
    }

    public function getMinLength($store = null)
    {
        return (int) Mage::getStoreConfig('ultradev_search/general/min_length', $store);
    }

    public function getCacheLifetime($store = null)
    {
        return (int) Mage::getStoreConfig('ultradev_search/general/cache_lifetime', $store);
    }

    public function getUseLocalStorage($store = null)
    {
        return Mage::getStoreConfigFlag('ultradev_search/general/use_local_storage', $store);
    }

    public function getJsPriceFormat()
    {
        return Mage::app()->getLocale()->getJsPriceFormat();
    }

    public function getBaseUrl()
    {
        return Mage::app()->getStore()->getBaseUrl();
    }

    public function getBaseUrlMedia()
    {
        return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl();
    }

    // ── Image Search ─────────────────────────────────────────────────────

    public function isImageSearchEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('ultradev_search/imagesearch/enable', $store);
    }

    public function getImageSearchProvider($store = null)
    {
        return Mage::getStoreConfig('ultradev_search/imagesearch/provider', $store);
    }

    public function getRequestLimit($store = null)
    {
        return (int) Mage::getStoreConfig('ultradev_search/imagesearch/request_limit', $store);
    }

    /**
     * Returns the active API key, switching to fallback if primary is exhausted.
     * Returns false if both keys are exhausted or not configured.
     */
    public function getActiveApiKey($store = null)
    {
        $model = Mage::getModel('ultradev_search/imageSearch');
        $count = $model->getMonthlyRequestCount();
        $limit = $this->getRequestLimit($store);

        $primary  = Mage::helper('core')->decrypt(
            Mage::getStoreConfig('ultradev_search/imagesearch/api_key_primary', $store)
        );
        $fallback = Mage::helper('core')->decrypt(
            Mage::getStoreConfig('ultradev_search/imagesearch/api_key_fallback', $store)
        );

        if ($count < $limit) {
            return $primary ?: false;
        }

        // Primary exhausted — try fallback
        if ($fallback) {
            return $fallback;
        }

        return false; // both exhausted
    }

    public function getImageSearchAnalyzeUrl()
    {
        return Mage::getUrl('ultradevsearch/imagesearch/analyze');
    }
}
