<?php
/**
 * Source model for AI provider select in admin
 */
class UltraDev_Search_Model_System_Config_Source_Provider
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'google_vision', 'label' => Mage::helper('ultradev_search')->__('Google Vision (1.000 req/mês grátis — recomendado)')),
            array('value' => 'gemini',        'label' => Mage::helper('ultradev_search')->__('Gemini Flash (1.500 req/dia grátis)')),
        );
    }
}
