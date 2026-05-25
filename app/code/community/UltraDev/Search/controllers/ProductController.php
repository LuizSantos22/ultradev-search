<?php
/**
 * UltraDev Search - Product JSON Controller
 *
 * @category    UltraDev
 * @package     UltraDev_Search
 * @version     1.0.0
 */
class UltraDev_Search_ProductController extends Mage_Core_Controller_Front_Action
{
    public function jsonAction()
    {
        $cacheId = 'ultradev_search_' . Mage::app()->getStore()->getId();

        if (false === ($data = Mage::app()->loadCache($cacheId))) {
            $collection = Mage::getModel('catalog/product')->getCollection();
            Mage::dispatchEvent('ultradev_search_product_collection_init', array('collection' => $collection));

            $data     = json_encode($collection->getData());
            $lifetime = Mage::helper('ultradev_search')->getCacheLifetime();
            Mage::app()->saveCache($data, $cacheId, array('block_html'), $lifetime);
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setBody($data);
    }
}
