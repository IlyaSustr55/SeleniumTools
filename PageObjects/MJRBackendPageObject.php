<?php

namespace Modera\Component\SeleniumTools\PageObjects;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Modera\Component\SeleniumTools\Behat\Context\HarnessAwareContext;
use Modera\Component\SeleniumTools\Querying\By;
use Modera\Component\SeleniumTools\Querying\ExtDeferredQueryHandler;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Provides a high-level abstraction to most common actions you will need to perform when writing tests for MJR
 * backend (https://mjr.dev.modera.org/).
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class MJRBackendPageObject extends HarnessAwareContext
{
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var ExtDeferredQueryHandler
     */
    private $deferredQueryHandler;

    /**
     * @param RemoteWebDriver $driver
     */
    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param string $username
     */
    public function typeInUsername($username)
    {
        $el = $this->driver->findElement(By::named(['field', 'User ID']));
        $el->clear();
        $el->sendKeys($username);
    }

    public function typeInUserEmail()
    {

        $el = $this->driver->findElements(By::named(['field', 'name@host.com']))[1];
        $el->clear();
        $el->sendKeys($this->getActor()->getUserEmail());
    }

    /**
     * @param string $password
     */
    public function typeInPassword($password, $type)
    {
        $el = $this->driver->findElements(By::named(['field', 'Password']))[$type];
        $el->clear();
        $el->sendKeys($password);
    }

    public function clickSignInButton($type)
    {
        $this->driver->findElements(By::named(['button', 'Sign in']))[$type]->click();
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        $this->driver->wait(50, 1000)->until(
            WebDriverExpectedCondition::visibilityOfAnyElementLocated(By::named(['field', 'Password']))
        );
        if (empty($this->driver->findElements(By::named(['field', 'name@host.com'])))) {
            $sleep = 500000; // half second
            $this->typeInUsername($username);
            usleep($sleep);
            $type = 0;
        } else {
            $sleep = 500000; // half second
            $this->typeInUserEmail();
            usleep($sleep);
            $type = 1;
        }
        $this->typeInPassword($password, $type);
        usleep($sleep);
        $this->clickSignInButton($type);
        usleep($sleep);
        try {
            $this->driver->wait(50, 1000)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(By::named(['field', 'Password']))
            );
        } catch (\Exception $e) {}
    }

    /**
     * @param string $label
     */
    public function clickMenuItemWithLabel($label)
    {
        $this->driver->findElement($this->getDeferredQueryHandler()->extComponentDomId("tab[text=$label]"))->click();
    }

    /**
     * @param string $label
     */
    public function clickTabItemWithLabel($label)
    {
        $this->driver->findElement($this->getDeferredQueryHandler()->extComponentDomId("tab[text^=$label]"))->click();
    }

    /**
     * @param string $label
     */
    public function clickToolsSectionWithLabel($label)
    {
        $this->driver->findElement(
            $this->getDeferredQueryHandler()->extDataviewColumnWithValue('dataview', 'name', $label)
        )->click();
    }

    /**
     * @return ExtDeferredQueryHandler
     */
    private function getDeferredQueryHandler()
    {
        if (!$this->deferredQueryHandler) {
            $this->deferredQueryHandler = new ExtDeferredQueryHandler($this->driver);
        }

        return $this->deferredQueryHandler;
    }
}