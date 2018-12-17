<?php
namespace VtigerTranslate;

use VtigerTranslate\GoogleTranslate;

// DS = '/';
// CR = "\n";
// TODO: Translate from Package
// TODO: Translate additional folders as cron
// TODO: option to pack as package .zip with manifest.xml

/**
 * vTiger Automatic Google Translator
 *
 * @author      Ido Kobelkowsky <ido@yalla-ya.com.com>
 * @link        http://www.yalla-ya.com/
 * @license     MIT
 */

class Translate {

    protected $header = '
    /*+**********************************************************************************
     * The contents of this file are subject to the vtiger CRM Public License Version 1.0
     * ("License"); You may not use this file except in compliance with the License
     * The Original Code is: vtiger CRM Open Source
     * The Initial Developer of the Original Code is vtiger.
     * Portions created by vtiger are Copyright (C) vtiger.
     * Portions created by Ido Kobelkowsky are Copyright (C) yalla ya!.
     * All Rights Reserved.
     *  ********************************************************************************
     *  Author       : Ido Kobelkowsky
     *  Translator   : Google Translate
     *  Created by   : vTiger Language Pack Translator
     *  Generator    : https://github.com/idokd/vtiger-language-pack-translator
     ************************************************************************************/
    ';
    protected $vtiger;
    protected $source;
    protected $target;
    protected $translator;
    protected $language_folder = '/languages/';
    protected $dummy = false;
    protected $overwrite = true;

    protected $permission = 0700;

    public function __construct($vtiger = '', $target = '', $source = 'en_us') {
        $this->vtiger = $vtiger; // TODO: remove trailing slash /
        $src = explode('_', $source);
        $tgt = explode('_', $target);
        $this->source = $source;
        $this->target = $target;
        $this->translator = new GoogleTranslate($src[0]);
        $this->translator->setSource($src[0]);
        $this->translator->setTarget($tgt[0]);
    }

    public function setOverwrite($overwrite) {
        $this->overwrite = $overwrite;
    }

    public function setDummy($dummy) {
        $this->dummy = $dummy;
    }

    public function full() {
        $source = $this->vtiger.$this->language_folder.$this->source;
        $target = $this->vtiger.$this->language_folder.$this->target;
        $this->scan($source, $target);
    }

    protected function scan($source, $target) {
        foreach (scandir($source) as $filename) {
            if (in_array($filename, array('.', '..'))) continue;
            $file = $source.'/'.$filename;
            $translated = $target.'/'.$filename;
            if (is_dir($file)) $this->scan($file, $translated);
            else {
                if ($this->overwrite || !file_exists($translated)) {
                    print 'Processing '.$filename."\n";
                    $this->file($file, $translated);
                }
            }
        }
    }

    public function module($module) {
        $source = $this->vtiger.$this->language_folder.$this->source.'/'.$module.'.php';
        $target = $this->vtiger.$this->language_folder.$this->target.'/'.$module.'.php';
        $this->file($source, $target);
    }

    public function file($source, $target) {
        $this->permission = fileperms(dirname($source));
        $languageStrings = null; $jsLanguageStrings = null;
        require_once($source);
        if ($languageStrings) foreach ($languageStrings as $key => $text)
            $languageStrings[$key] = $this->translate($text);
        if ($jsLanguageStrings) foreach ($jsLanguageStrings as $key => $text)
            $jsLanguageStrings[$key] = $this->translate($text);
        $content = $this->content($languageStrings, $jsLanguageStrings);
        if (!$this->dummy) return($this->write($target, $content));
        else print $content;
    }

    protected function content($languageStrings, $jsLanguageStrings) {
        $content = '<?php '."\n".$this->header."\n";
        if ($languageStrings) $content .= '$languageStrings = '.var_export($languageStrings, true).";\n";
        if ($jsLanguageStrings)  $content .= '$jsLanguageStrings = '.var_export($jsLanguageStrings, true).";\n";
        return($content);
    }

    protected function write($target, $content) {
      if (!is_dir(dirname($target))) mkdir(dirname($target), $this->permission, true);
      return(file_put_contents($target, $content));
    }

    public function translate($text) {
        return($this->translator->translate($text));
    }

}
