<?php
/**
 * UltraDev Search - Image Search Controller
 * Proxy: receives image from browser, calls AI provider, returns query string.
 * The API key never leaves the server.
 *
 * @category    UltraDev
 * @package     UltraDev_Search
 * @version     1.0.0
 */
class UltraDev_Search_ImagesearchController extends Mage_Core_Controller_Front_Action
{
    public function analyzeAction()
    {
        $this->getResponse()->setHeader('Content-Type', 'application/json', true);

        $helper = Mage::helper('ultradev_search');

        if (!$helper->isEnabled() || !$helper->isImageSearchEnabled()) {
            return $this->getResponse()->setBody(json_encode(array('error' => 'disabled')));
        }

        if (!$helper->getActiveApiKey()) {
            return $this->getResponse()->setBody(json_encode(array('error' => 'limit_reached')));
        }

        // Read raw POST body (base64 JSON sent by browser)
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (empty($data['image']) || empty($data['mime_type'])) {
            $this->getResponse()->setHttpResponseCode(400);
            return $this->getResponse()->setBody(json_encode(array('error' => 'invalid_request')));
        }

        $base64   = $data['image'];
        $mimeType = $data['mime_type'];

        // Basic validation
        $allowedMimes = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
        if (!in_array($mimeType, $allowedMimes)) {
            $this->getResponse()->setHttpResponseCode(400);
            return $this->getResponse()->setBody(json_encode(array('error' => 'invalid_mime')));
        }

        $query = Mage::getModel('ultradev_search/imageSearch')->analyze($base64, $mimeType);

        if ($query === false) {
            $this->getResponse()->setHttpResponseCode(503);
            return $this->getResponse()->setBody(json_encode(array('error' => 'provider_error')));
        }

        return $this->getResponse()->setBody(json_encode(array('query' => $query)));
    }
}
