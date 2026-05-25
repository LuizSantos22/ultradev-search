<?php
/**
 * UltraDev Search Observer
 *
 * @category    UltraDev
 * @package     UltraDev_Search
 * @version     1.0.0
 */
class UltraDev_Search_Model_Observer
{
    /**
     * Attached to: ultradev_search_product_collection_init
     */
    public function onProductCollectionInit(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $observer->getEvent()->getCollection();
        $collection->addAttributeToFilter('name', array('notnull' => true))
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter('thumbnail', array('notnull' => true))
            ->addAttributeToFilter('url_path', array('notnull' => true))
            ->addStoreFilter()
            ->addPriceData()
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());
    }
}
