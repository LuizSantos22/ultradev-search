<?php
class UltraDev_Search_Model_System_Config_Source_GeminiModel
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'gemini-2.5-flash-lite', 'label' => 'Gemini 2.5 Flash Lite (faster, free)'),
            array('value' => 'gemini-2.5-flash',      'label' => 'Gemini 2.5 Flash (balanced, free)'),
            array('value' => 'gemini-3.5-flash',      'label' => 'Gemini 3.5 Flash (latest, preview)'),
        );
    }
}
