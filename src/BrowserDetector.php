<?php

namespace Elao\BrowserDetector;

/**
 * Browser Detector
 */
class BrowserDetector
{
    const BROWSER_INCOMPATIBLE         = 0;
    const BROWSER_PARTIALLY_COMPATIBLE = 1;
    const BROWSER_COMPATIBLE           = 2;

    /**
     * Is Browscap enabled
     *
     * @var boolean
     */
    private $browscapEnabled;

    /**
     * Browser
     *
     * @var Browser
     */
    private $browser;

    /**
     * Browsers requirements
     *
     * @var array
     */
    private $requirements;

    /**
     * Browser compatibility
     *
     * @var boolean
     */
    private $compatibility;

    /**
     * Config
     *
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param boolean $browscapEnabled Is browscap enabled
     */
    public function __construct($browscapEnabled)
    {
        $this->browscapEnabled = $browscapEnabled;
        $this->browser         = new Browser();
        $this->requirements    = array(
            'incompatible'         => array(),
            'partially_compatible' => array(),
        );
    }

    /**
     * Load configuration
     *
     * @param array $config
     */
    public function loadConfiguration($config)
    {
        $this->config = $config;
        foreach ($config as $support => $requirement) {
            foreach ($requirement as $name => $version) {
                $this->requirements[$support][ucwords($name)] = $this->parseVersion($version);
            }
        }
    }

    /**
     * Parse version
     *
     * @param string $version
     *
     * @return array
     */
    public function parseVersion($version)
    {
        if (!empty($version) && preg_match('/^([<>]=?)?(.+)/', $version, $matches)) {
            $versionNumber = floatval($matches[2]);

            switch ($matches[1]) {
                case '<=':
                    $test = 'isEqualOrEarlierThan';
                    break;
                case '<':
                    $test = 'isEarlierThan';
                    break;
                case '>=':
                    $test = 'isEqualOrLaterThan';
                    break;
                case '>':
                    $test = 'isLaterThan';
                    break;
                default:
                    $test = 'isExactly';
                    break;
            }

            return array(
                'test'    => $test,
                'version' => $versionNumber,
            );
        } else {
            return array();
        }
    }

    /**
     * Set the user agent
     *
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        if ($browser = $this->createBrowserFromUseragent($userAgent)) {
            $this->setBrowser($browser);
        }
    }

    /**
     * Create Browser from user-agent directive
     *
     * @param  string $userAgent
     *
     * @return Browser
     */
    public function createBrowserFromUserAgent($userAgent)
    {
        $data = $this->browscapEnabled ? get_browser($userAgent, true) : array();

        return new Browser($data);
    }

    /**
     * Get the current Browser
     *
     * @return Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Set browser
     *
     * @param Browser $browser
     */
    public function setBrowser(Browser $browser)
    {
        $this->browser       = $browser;
        $this->compatibility = self::BROWSER_COMPATIBLE;

        foreach ($this->requirements['incompatible'] as $browser => $req) {
            if ($this->browser->matches($browser, $req)) {
                $this->compatibility = self::BROWSER_INCOMPATIBLE;
                break;
            }
        }

        if ($this->compatibility !== self::BROWSER_INCOMPATIBLE) {
            foreach ($this->requirements['partially_compatible'] as $browser => $req) {
                if ($this->browser->matches($browser, $req)) {
                    $this->compatibility = self::BROWSER_PARTIALLY_COMPATIBLE;
                    break;
                }
            }
        }
    }

    /**
     * Is the current browser compatible ?
     *
     * @return boolean
     */
    public function isCompatible()
    {
        return $this->compatibility === self::BROWSER_COMPATIBLE;
    }

    /**
     * Is the current browser partially compatible ?
     *
     * @return boolean
     */
    public function isPartiallyCompatible()
    {
        return $this->compatibility === self::BROWSER_PARTIALLY_COMPATIBLE;
    }

    /**
     * Is the current browser incompatible ?
     *
     * @return boolean
     */
    public function isIncompatible()
    {
        return $this->compatibility === self::BROWSER_INCOMPATIBLE;
    }

    /**
     * Get the config
     *
     * @return array
     */
    public function getConfig()
    {
       return $this->config;
    }
}
