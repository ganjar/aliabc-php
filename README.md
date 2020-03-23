# aliabc-php
**Application Language Integration ABC** - php library

## Installation

```bash
$ composer require ganjar/aliabc-php
```

## Quick start
First of all, you need choose type of translation source, which you will be used.
From the box, in this packet you may use:
* MysqlSource
* CsvSource

For first use simplification , we created `QuickStartALIAbFactory`, which creates for you instance of `ALIAbc`, which is facade Class, with general configuration.<br>
Exist two base type of using this packet:
* with html auto translation. In this cast you may put to buffer full html text, and ALIAb search and translate all phrases
    * With MySql source 
    ```php
    $aliAbc = (new QuickStartALIAbFactory)->createHtmlBufferMysqlSource((new PDO('mysql:dbname=test;host=mysql', 'root', 'root')),'en','ua');
    ```
    * With CSV source 
    ```php
    $aliAbc = (new QuickStartALIAbFactory)->createHtmlBufferCsvSource('/path/to/writable/directory/for/translation','en','ua'));
    ```
* manually adding text for translation in html
    * With MySql source 
    ```php
    $aliAbc = (new QuickStartALIAbFactory)->createMysqlSource((new PDO('mysql:dbname=test;host=mysql', 'root', 'root')),'en','ua');
    ```
    * With CSV source 
    ```php
    $aliAbc = (new QuickStartALIAbFactory)->createCsvSource('/path/to/writable/directory/for/translation','en','ua'))
    ```


## Basic Usage

```php
$aliAbc = (new QuickStartALIAbFactory)->createHtmlBufferMysqlSource((new PDO('mysql:dbname=test;host=mysql', 'root', 'root')),'en','ua');
$aliAbc->saveTranslate('Hello', 'Привіт');

// Dirrect translation
echo $aliAbc->translate('Hello');
var_dump($aliAbc->translateAll(['Hello']));

// Translate in html, using buffer, for translation at end, by one request for Source
$html =  '<div>' . $aliAbc->addToBuffer('Hello') . '</div>';
echo $html; // '<div>#ali-buffer-layer-content_0#</div>'
echo $aliAbc->translateBuffer($html); // '<div>Привіт</div>'

// If you choose type with auto html translation, you may put full html code for tanslate
$html =  $aliAbc->addToBuffer('<div>Hello</div>');
echo $aliAbc->translateBuffer($html); // '<div>Привіт</div>'


// Also you may discover object $aliAbc->getBufferCaptcher() for additional method for work with buffer
$aliAbc->getBufferCaptcher()->start();
echo '<div>Hello</div>';
$aliAbc->getBufferCaptcher()->end();
echo $aliAbc->translateBuffer(); // '<div>Привіт</div>'
```
