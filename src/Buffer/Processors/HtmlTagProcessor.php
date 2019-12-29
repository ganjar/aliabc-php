<?php

namespace ALI\Buffer\Processors;

/**
 * Class HtmlTagProcessor
 * @package ALI\Buffer\Processors
 */
class HtmlTagProcessor extends AbstractHtmlProcessor
{
    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    public function getFindPhrasesRegex()
    {
        //test regex https://regex101.com/r/aOX8Fo/2
        return '$
          (?:>|\A)                                      #Close tag symbol or start of string
                (?:(?:&\#?[a-z0-9]{1,7};)|[^\w<])*      #Html entities and untranslated symbols 
            		(?<original>[\w][^<>]+)             #Translate content
                (?:(?:&\#?[a-z0-9]{1,7};)|\s)*          #Html entities and spaces
  		  (?:<|\Z)                                      #Open tag symbol or end of string
        $Uxusi';
    }
}