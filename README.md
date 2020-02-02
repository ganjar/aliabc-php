# aliabc-php
**Application Language Integration ABC** - php library

## Installation

```bash
$ composer require ganjar/aliabc-php
```

## Basic Usage

```php
<?php

//Set translation source - MySQL
//$connection = new PDO("mysql:dbname=test;host=localhost", 'root', 'root');
//$aliTranslateSource = new \ALI\Translate\Sources\MySqlSource($connection);
//if (!$aliTranslateSource->isInstalled()) {
//    $aliTranslateSource->install();
//}

//Set CSV files as a translation source. Files in dir /lng/ with comma delimiter
$fileSource = new \ALI\Translate\Sources\CsvFileSource(__DIR__ . '/lng/', ",", 'txt');

//Parse language
$languageAlias = false;
if (preg_match('#^/(?<language>\w{2})/#', $_SERVER['REQUEST_URI'], $parseUriMatches)) {
    $languageAlias = $parseUriMatches['language'];
}

//Set language
$language = new \ALI\Translate\Language\Language(
    $languageAlias, 
    '', 
    $languageAlias == 'en' || !$languageAlias
);

//Make Translate instance
$translate = new \ALI\Translate\Translate(
    $language,
    $fileSource
);
$translate->addOriginalProcessor(new \ALI\Translate\OriginalProcessors\ReplaceNumbersOriginalProcessor());

//BufferTranslate - class for parse and translate phrases in content
$bufferTranslate = new \ALI\Buffer\BufferTranslate($translate);

//PreProcessors - hide some content parts from buffer processors
$bufferTranslate->addPreProcessor(new \ALI\Buffer\PreProcessors\IgnoreHtmlTagsPreProcessor(['style', 'script']));
$bufferTranslate->addPreProcessor(new \ALI\Buffer\PreProcessors\HtmlCommentPreProcessor());
$bufferTranslate->addPreProcessor(new \ALI\Buffer\PreProcessors\SliIgnoreTagPreProcessor());

//Add buffer processor for parse content in HTML tags
$bufferTranslate->addProcessor(new \ALI\Buffer\Processors\HtmlTagProcessor());

//Add buffer processor for parse phrases in custom tags
//$bufferTranslate->addProcessor(new CustomTagProcessor('[[', ']]'));

//Add processor for translate html attributes content
$aliHtmlAttributesProcessor = new \ALI\Buffer\Processors\HtmlAttributesProcessor();
$aliHtmlAttributesProcessor->setAllowAttributes(['title', 'alt', 'rel']);
$bufferTranslate->addProcessor($aliHtmlAttributesProcessor);

//Add processor for replace language in URLs
$bufferTranslate->addProcessor(new \ALI\Buffer\Processors\HtmlLinkProcessor());

$ali = new \ALI\ALIAbc();
$ali->setTranslate($translate);
$ali->setBufferTranslate($bufferTranslate);

//Use buffers
$ali->iniSourceBuffering();

//start/end
$ali->getBuffer()->start();
echo '<b>Hello word</b>';
$ali->getBuffer()->end();

//Without translate because outside buffer
echo '<b>Without translate content</b>';

$ali->getBuffer()->start();
echo '<b>Translated content inside buffer</b>';
$ali->getBuffer()->end();

//simple add buffer
echo $ali->getBuffer()->add('<b>Hello word 3</b>');

//Buffering all content inside callback function
echo $ali->getBuffer()->buffering(function () {
    echo '<b>Hello word 4</b>';
});

//Quick translation of a specific phrase
echo $ali->getTranslate()->translate('Hello word');

//Buffering all next content
$ali->getBuffer()->start();

//Fast translate
//echo $ali->getTranslate()->translate('Hello word');
//Save translate
//$ali->getTranslate()->saveTranslate($language, 'Hello word', 'Привет мир');

return $ali;