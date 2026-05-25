<?php
/**
 * UltraDev Search - Image Search Model
 * Handles provider calls (Google Vision / Gemini) and request counting.
 *
 * @category    UltraDev
 * @package     UltraDev_Search
 * @version     1.0.0
 */
class UltraDev_Search_Model_ImageSearch extends Mage_Core_Model_Abstract
{
    const COUNT_KEY = 'ultradev_search/imagesearch/request_count';
    const MONTH_KEY = 'ultradev_search/imagesearch/request_count_month';

    // ── Request counter ──────────────────────────────────────────────────

    public function getMonthlyRequestCount()
    {
        $savedMonth = Mage::getStoreConfig(self::MONTH_KEY);
        $currentMonth = date('Y-m');

        if ($savedMonth !== $currentMonth) {
            $this->_saveConfig(self::COUNT_KEY, 0);
            $this->_saveConfig(self::MONTH_KEY, $currentMonth);
            return 0;
        }

        return (int) Mage::getStoreConfig(self::COUNT_KEY);
    }

    public function incrementRequestCount()
    {
        $count = $this->getMonthlyRequestCount() + 1;
        $this->_saveConfig(self::COUNT_KEY, $count);
        return $count;
    }

    protected function _saveConfig($path, $value)
    {
        Mage::getConfig()->saveConfig($path, $value, 'default', 0);
        Mage::getConfig()->cleanCache();
    }

    // ── Provider calls ───────────────────────────────────────────────────

    /**
     * Analyze image and return search query string.
     *
     * @param  string $base64   Base64-encoded image data
     * @param  string $mimeType e.g. image/jpeg
     * @return string|false
     */
    public function analyze($base64, $mimeType = 'image/jpeg')
    {
        $helper   = Mage::helper('ultradev_search');
        $apiKey   = $helper->getActiveApiKey();
        $provider = $helper->getImageSearchProvider();

        if (!$apiKey) {
            return false;
        }

        try {
            if ($provider === 'gemini') {
                $query = $this->_callGemini($base64, $mimeType, $apiKey);
            } else {
                $query = $this->_callGoogleVision($base64, $apiKey);
            }

            if ($query) {
                $this->incrementRequestCount();
            }

            return $query;

        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    // ── Google Vision ────────────────────────────────────────────────────

    protected function _callGoogleVision($base64, $apiKey)
    {
        $url = 'https://vision.googleapis.com/v1/images:annotate?key=' . urlencode($apiKey);

        $payload = json_encode(array(
            'requests' => array(array(
                'image'    => array('content' => $base64),
                'features' => array(
                    array('type' => 'LOGO_DETECTION',  'maxResults' => 3),
                    array('type' => 'LABEL_DETECTION', 'maxResults' => 5),
                    array('type' => 'WEB_DETECTION',   'maxResults' => 3),
                ),
            ))
        ));

        $response = $this->_httpPost($url, $payload);
        if (!$response) return false;

        $data = json_decode($response, true);
        if (!isset($data['responses'][0])) return false;

        $r     = $data['responses'][0];
        $terms = array();

        // 1. Logos (highest priority — brand names)
        if (!empty($r['logoAnnotations'])) {
            foreach ($r['logoAnnotations'] as $logo) {
                $terms[] = $logo['description'];
            }
        }

        // 2. Web entities (product-level recognition)
        if (!empty($r['webDetection']['webEntities'])) {
            foreach (array_slice($r['webDetection']['webEntities'], 0, 3) as $entity) {
                if (!empty($entity['description'])) {
                    $terms[] = $entity['description'];
                }
            }
        }

        // 3. Labels as fallback
        if (count($terms) < 2 && !empty($r['labelAnnotations'])) {
            foreach (array_slice($r['labelAnnotations'], 0, 2) as $label) {
                $terms[] = $label['description'];
            }
        }

        $terms = array_unique($terms);
        return implode(' ', array_slice($terms, 0, 3));
    }

    // ── Gemini Flash ─────────────────────────────────────────────────────

    protected function _callGemini($base64, $mimeType, $apiKey)
    {
        $model = Mage::getStoreConfig('ultradev_search/image_search/gemini_model') ?: 'gemini-2.5-flash-lite';
        $url   = 'https://generativelanguage.googleapis.com/v1beta/models/'
               . $model . ':generateContent?key=' . urlencode($apiKey);

        $prompt = 'Identifique o produto nesta imagem. Retorne APENAS a marca e o modelo '
                . 'se visível, separados por espaço. A marca tem prioridade máxima. '
                . 'Máximo 3 palavras. Exemplos: "Ozlo Sleepbuds" ou "Nike Barcelona" ou '
                . '"JBL Flip". Sem tipo de produto genérico, sem explicações, sem pontuação.';

        $payload = json_encode(array(
            'contents' => array(array(
                'parts' => array(
                    array('inline_data' => array('mime_type' => $mimeType, 'data' => $base64)),
                    array('text' => $prompt),
                )
            ))
        ));

        $response = $this->_httpPost($url, $payload);
        if (!$response) return false;

        $data = json_decode($response, true);
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) return false;

        return trim($data['candidates'][0]['content']['parts'][0]['text']);
    }

    // ── HTTP helper ──────────────────────────────────────────────────────

    protected function _httpPost($url, $jsonPayload)
    {
        $client = new Zend_Http_Client($url, array(
            'timeout'     => 15,
            'useragent'   => 'UltraDev_Search/1.0',
        ));
        $client->setRawData($jsonPayload, 'application/json');
        $response = $client->request(Zend_Http_Client::POST);

        if (!$response->isSuccessful()) {
            Mage::log('UltraDev_Search: HTTP ' . $response->getStatus() . ' — ' . $response->getBody(), Zend_Log::ERR);
            return false;
        }

        return $response->getBody();
    }
}
