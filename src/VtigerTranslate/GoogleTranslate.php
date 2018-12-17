<?php

namespace VtigerTranslate;

use BadMethodCallException;
use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use UnexpectedValueException;

/**
 * vTiger Automatic Google Translator
 *
 * Google Translator originally taken and modified from
 * https://github.com/Stichoza/google-translate-php
 * By original author: Levan Velijanashvili <me@stichoza.com> - http://stichoza.com/
 *
 * @author      Ido Kobelkowsky <ido@yalla-ya.com.com>
 * @link        http://www.yalla-ya.com/
 * @license     MIT
 */

class GoogleTranslate
{
    /**
     * @var \GuzzleHttp\Client HTTP Client
     */
    protected $client;

    /**
     * @var string|null Source language - from where the string should be translated
     */
    protected $source;

    /**
     * @var string Target language - to which language string should be translated
     */
    protected $target;

    /**
     * @var string|null Last detected source language
     */
    protected $lastDetectedSource;

    /**
     * @var string Google Translate URL base
     */
    protected $url = 'https://translation.googleapis.com/language/translate/v2';

    /**
     * @var array Dynamic GuzzleHttp client options
     */
    protected $options = [];

    /**
     * @var array Regex key-value patterns to replace on response data
     */
    protected $resultRegexes = [
        '/,+/'  => ',',
        '/\[,/' => '[',
    ];

    /**
     * Class constructor.
     *
     * For more information about HTTP client configuration options, see "Request Options" in
     * GuzzleHttp docs: http://docs.guzzlephp.org/en/stable/request-options.html
     *
     * @param string $target Target language
     * @param string|null $source Source language
     * @param array|null $options Associative array of http client configuration options
     */
    public function __construct(string $target = 'en', string $source = null, array $options = null)
    {
        $this->client = new Client();
        $this->setOptions($options) // Options are already set in client constructor tho.
            ->setSource($source)
            ->setTarget($target);
    }

    /**
     * Set target language for translation.
     *
     * @param string $target Language code
     * @return GoogleTranslate
     */
    public function setTarget(string $target) : self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Set source language for translation.
     *
     * @param string|null $source Language code
     * @return GoogleTranslate
     */
    public function setSource(string $source = null) : self
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Set Google Translate URL base
     *
     * @param string $url Google Translate URL base
     * @return GoogleTranslate
     */
    public function setUrl(string $url) : self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set GuzzleHttp client options.
     *
     * @param array $options guzzleHttp client options.
     * @return GoogleTranslate
     */
    public function setOptions(array $options = null) : self
    {
        $this->options = $options ?? [];
        return $this;
    }

    /**
     * Get last detected source language
     *
     * @return string|null Last detected source language
     */
    public function getLastDetectedSource()
    {
        return $this->lastDetectedSource;
    }

    /**
     * Override translate method for static call.
     *
     * @param string $string
     * @param string $target
     * @param string|null $source
     * @param array $options
     * @return null|string
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public static function trans(string $string, string $target = 'en', string $source = null, array $options = [])
    {
        return (new self)
            ->setOptions($options) // Options are already set in client constructor tho.
            ->setSource($source)
            ->setTarget($target)
            ->translate($string);
    }

    /**
     * Translate text.
     *
     * This can be called from instance method translate() using __call() magic method.
     * Use $instance->translate($string) instead.
     *
     * @param string $string String to translate
     * @return string|null
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public function translate(string $string) : string
    {
        // TODO: support an array of strings to translate varios at the time
        $responseArray = $this->getResponse($string);
        /*
         * if response in text and the content has zero the empty returns true, lets check
         * if response is string and not empty and create array for further logic
         */

        // Check if translation exists
        if (!isset($responseArray['data']) || empty($responseArray['data'])) {
            return null;
        }

        // Detect languages
        $detectedLanguages = [];

        // Set initial detected language to null
        $this->lastDetectedSource = null;

        if (isset($responseArray['data']['translations'][0]['detectedSourceLanguage']) && !empty($responseArray['data']['translations'][0]['detectedSourceLanguage'])) {
            $this->lastDetectedSource = $responseArray['data']['translations'][0]['detectedSourceLanguage'];
        }

        // the response can be sometimes an translated string.
        if (is_string($responseArray)) {
            return $responseArray;
        } else {
            return $responseArray['data']['translations'][0]['translatedText'];
        }
    }

    /**
     * Get response array.
     *
     * @param string $string String to translate
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     * @return array|string Response
     */
    public function getResponse(string $string) : array
    {
        $queryBodyArray = [
            'target'   => $this->target,
            'key'   => 'AIzaSyCi8EPqnMCpdFj1D7VaalVb6dpRTrbhj0w',
            'format' => 'text',
            'q' => $string,
        ];
        if ($this->source) $queryBodyArray['source'] = $this->source;
        try {
            $response = $this->client->post($this->url, [
                    'form_params'  => $queryBodyArray,
                ] + $this->options);
        } catch (RequestException $e) {
            throw new ErrorException($e->getMessage());
        }

        $body = $response->getBody(); // Get response body

        // Modify body to avoid json errors
        $bodyJson = preg_replace(array_keys($this->resultRegexes), array_values($this->resultRegexes), $body);

        // Decode JSON data
        if (($bodyArray = json_decode($bodyJson, true)) === null) {
            throw new UnexpectedValueException('Data cannot be decoded or it is deeper than the recursion limit');
        }

        return $bodyArray;
    }

    /**
     * Check if given locale is valid.
     *
     * @param string $lang Langauge code to verify
     * @return bool
     */
    protected function isValidLocale(string $lang) : bool
    {
        return (bool) preg_match('/^([a-z]{2})(-[A-Z]{2})?$/', $lang);
    }
}
