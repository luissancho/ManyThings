<?php

namespace ManyThings\Core;

use Smarty;

class SmartyML extends Smarty
{
    public $language;

    public function __construct($locale = '')
    {
        parent::__construct();

        $this->setLanguage($locale);
    }

    public function setLanguage($locale)
    {
        // Multilanguage Support
        // use $smarty->language->setLocale() to change the language of your template
        // $smarty->loadTranslationTable() to load custom translation tables
        $this->language = new ngLanguage($locale); // Create a new language object
        $GLOBALS['_LANGUAGES_'] = &$this->language;
        $this->registerFilter('pre', [$this, 'prefilter_i18n']);
    }

    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
    {
        // We need to set the cache id and the compile id so a new script will be
        // compiled for each language. This makes things really fast ;-)
        $compile_id = $this->language->getCurrentLanguage() . '-' . $compile_id;
        $cache_id = $compile_id;

        // Now call parent method
        return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }

    /*
    * Test to see if valid cache exists for this template
    * @param string $tpl_file name of template file
    * @param string $cache_id
    * @param string $compile_id
    * @return string|false results of {@link _read_cache_file()}
    */
    public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if (!$this->caching) {
            return false;
        }

        if (!isset($compile_id)) {
            $compile_id = $this->language->getCurrentLanguage() . '-' . $this->compile_id;
            $cache_id = $compile_id;
        }

        return parent::isCached($template, $cache_id, $compile_id, $parent);
    }

    /*
    * Takes the language file, and rips it into the template
    */
    public function prefilter_i18n($tpl_source, $smarty)
    {
        if (!is_object($GLOBALS['_LANGUAGES_'])) {
            die('Error loading Multilanguage Support');
        }
        // Load translations (if needed)
        $GLOBALS['_LANGUAGES_']->loadCurrentTranslationTable();
        // Now replace the matched language strings with the entry in the file
        return preg_replace_callback('/##(.+?)##/', [$this, 'compileLang'], $tpl_source);
    }

    /*
    * Processes every language identifier and inserts the language string in its place
    */
    public function compileLang($key)
    {
        return $GLOBALS['_LANGUAGES_']->getTranslation($key[1]);
    }
}

class ngLanguage
{
    public $translationTable; // Currently loaded translation table
    public $currentLanguage; // Currently loaded language

    public function __construct($locale)
    {
        $this->translationTable = [];
        $this->translationTable[$locale] = [];
        $this->setCurrentLanguage($locale);
    }

    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }

    public function setCurrentLanguage($locale)
    {
        $this->currentLanguage = $locale;
    }

    public function loadTranslationTable($locale)
    {
        $path = ABSPATH . 'templates/languages/' . $this->getCurrentLanguage() . '/global.lng';

        if (file_exists($path)) {
            $entries = file($path);
            $this->translationTable[$locale] = [];

            foreach ($entries as $row) {
                // Ignore comments
                if (strpos($row, '##') !== false) {
                    continue;
                }

                $keyValuePair = explode(' = ', $row);

                // Multiline values: the first line with an equal sign '=' will start a new 'key = value' pair
                if (count($keyValuePair) == 1) {
                    $this->translationTable[$locale][$key] .= ' ' . rtrim($keyValuePair[0]);
                    continue;
                }

                $key = trim($keyValuePair[0]);
                $value = $keyValuePair[1];

                if (!empty($key)) {
                    $this->translationTable[$locale][$key] = rtrim($value);
                }
            }

            return true;
        }

        return false;
    }

    public function loadCurrentTranslationTable()
    {
        $this->loadTranslationTable($this->getCurrentLanguage());
    }

    public function getTranslation($key)
    {
        $trans = $this->translationTable[$this->getCurrentLanguage()];

        if (is_array($trans) && count($trans) > 0) {
            if (isset($trans[$key])) {
                return $trans[$key];
            }
        }

        return $key;
    }
}
