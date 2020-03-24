<?php

namespace ALI\Translate\TranslateProcessors;

/**
 * Class ReplaceNumbersTranslateProcessor
 * @package ALI\Translate\TranslateProcessors
 */
class ReplaceNumbersTranslateProcessor implements TranslateProcessorInterface
{
    /**
     * @param string $original
     * @param string $translate
     * @return string
     */
    public function process($original, $translate)
    {
        preg_match_all('#(?:[\d])+#u', $original, $symbols);
        preg_match_all('#(?:[\d])+#u', $translate, $tSymbols);

        $symbols = $symbols[0];
        $tSymbols = $tSymbols[0];

        if (!empty($symbols)) {

            $sPos = 0;
            foreach ($symbols as $symbolKey => $symbol) {

                if (!isset($tSymbols[$symbolKey])) {
                    continue;
                }

                $sPos = strpos($translate, $tSymbols[$symbolKey], $sPos);

                if ($sPos !== false) {
                    $translate = substr_replace($translate, $symbol, $sPos, strlen($tSymbols[$symbolKey]));
                    $sPos += strlen($symbol);
                }
            }
        }

        return $translate;
    }
}
