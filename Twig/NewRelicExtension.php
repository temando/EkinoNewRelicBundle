<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Twig;

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

/**
 * Twig extension to manually include BrowserTimingHeader and BrowserTimingFooter into twig templates
 */
class NewRelicExtension extends \Twig_Extension
{
    /**
     * @var \Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic
     */
    protected $newRelic;

    /**
     * @var \Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface
     */
    protected $interactor;

    /**
     * @var bool
     */
    protected $instrument;

    /**
     * @var bool
     */
    protected $headerCalled = false;

    /**
     * @var bool
     */
    protected $footerCalled = false;

    /**
     * @param NewRelic                    $newRelic
     * @param NewRelicInteractorInterface $interactor
     * @param bool                        $instrument
     */
    public function __construct(
        NewRelic $newRelic,
        NewRelicInteractorInterface $interactor,
        $instrument = false
    ) {
        $this->newRelic = $newRelic;
        $this->interactor = $interactor;
        $this->instrument = $instrument;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ekino_newrelic_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'getNewrelicBrowserTimingHeader' => new \Twig_Function_Method($this, 'ekino_newrelice_browser_timing_header'),
            'getNewrelicBrowserTimingFooter' => new \Twig_Function_Method($this, 'ekino_newrelice_browser_timing_footer'),
        );
    }

    /**
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getBrowserTimingHeader()
    {
        if ($this->isHeaderCalled()) {
            throw new \RuntimeException('Function "ekino_newrelice_browser_timing_header" has already been called');
        }

        $this->prepareInteractor();

        $this->headerCalled = true;

        return $this->interactor->getBrowserTimingHeader();
    }

    /**
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getBrowserTimingFooter()
    {
        if ($this->isFooterCalled()) {
            throw new \RuntimeException('Function "ekino_newrelice_browser_timing_footer" has already been called');
        }

        if (false === $this->isHeaderCalled()) {
            $this->prepareInteractor();
        }

        $this->footerCalled = true;

        return $this->interactor->getBrowserTimingFooter();
    }

    /**
     * @return bool
     */
    public function isHeaderCalled()
    {
        return $this->headerCalled;
    }

    /**
     * @return bool
     */
    public function isFooterCalled()
    {
        return $this->footerCalled;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->isHeaderCalled() || $this->isFooterCalled();
    }

    protected function prepareInteractor()
    {
        if ($this->instrument) {
            $this->interactor->disableAutoRUM();
        }

        foreach ($this->newRelic->getCustomMetrics() as $name => $value) {
            $this->interactor->addCustomMetric($name, $value);
        }

        foreach ($this->newRelic->getCustomParameters() as $name => $value) {
            $this->interactor->addCustomParameter($name, $value);
        }
    }
}
