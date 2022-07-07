<?php

namespace Modera\Component\SeleniumTools\Querying;

use Facebook\WebDriver\Exception\UnsupportedOperationException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use http\Exception\UnexpectedValueException;
use Modera\Component\SeleniumTools\Exceptions\NoElementFoundException;
use mysql_xdevapi\Exception;

/**
 * Contains ExtJs related query methods.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ExtDeferredQueryHandler
{
    /**
     * @var RemoteWebDriver
     */
    private $driver;

    /**
     * @param RemoteWebDriver $driver
     */
    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Attempts to resolve DOM id of component which matches Ext.ComponentQuery.query() compatible $query
     *
     * @param string $query  ExtJs Ext.ComponentQuery.query() method compatible query
     * @param int $timeout  How long to wait for a component with $query to become discoverable
     *
     * @return string  DOM element ID that represents a first component resolved by given $query
     */
    public function extComponentDomId($query, $timeout = 60)
    {
        $startTime = time();

        // although ExtJs has already generated DOM element ID it may not be rendered yet as of now ...
        $id = $this->doRunWhenComponentAvailable(
            $query,
            'return result[0].id;',
            $startTime,
            $timeout
        );

        $id = WebDriverBy::id($id);

        // so we are waiting for some time until ExtJs has generated required Dom and flushed it so
        // we can really access and manipulate it
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated($id));

        return $id;
    }

    /**
     * Runs $stmt when a Ext.ComponentQuery.query() compatible $query returns at least one match.
     *
     * @param string $query  ExtJS Ext.ComponentQuery.query() compatible query
     * @param string $stmt  A JavaScript statement that needs to be executed when at least one component is returned by
     *                      the $query. You can access to returned components using "result" variable. NB! In order
     *                      to terminate execution of the javascript method successfully you need to return
     *                      that the $stmt would return TRUE.
     * @param int $timeout  Maximum wait time for at least one component to become available
     *
     * @return string
     */
    public function runWhenComponentAvailable($query, $stmt = 'return true;', $timeout = 60)
    {
        return $this->doRunWhenComponentAvailable($query, $stmt, time(), $timeout);
    }

    /**
     * Runs $stmt when a Ext.ComponentQuery.query() compatible $query returns at least one match and this
     * component has store, store is loaded and not empty
     *
     * @param string $query  ExtJS Ext.ComponentQuery.query() compatible query
     * @param string $stmt  A JavaScript statement that needs to be executed when at least one component is returned by
     *                      the $query. You can access to returned components using "result" variable. NB! In order
     *                      to terminate execution of the javascript method successfully you need to return
     *                      that the $stmt would return TRUE.
     * @param int $timeout  Maximum wait time for at least one component to become available
     *
     * @return string
     */
    public function runWhenStoreForComponentAvailable($query, $stmt = 'return true;', $timeout = 60)
    {
        // If we return a boolean value from a function then we will get
        // "java.lang.Boolean cannot be cast to java.lang.String" exception by Selenium, so to address this issue
        // we are returning a 'false' as a string instead
        $js = <<<'JST'
%function_name% = function () {
    var result = [];
    var components = Ext.ComponentQuery.query("%query%");
    Ext.each(components, function(component) {
        if (component.isVisible(true) && component.getStore()
         && !component.getStore().isLoading() && component.getStore().getCount()) {
            result.push(component);
        }
    });

    if (result.length > 0) {
        var firstCmp = result[0];

        %stmt%
    }

    return 'false';
};
JST;

        return $this->doRunWhenComponentAvailable($query, $stmt, time(), $timeout, $js);
    }

    /**
     * @param string $query
     * @param int $timeout
     */
    public function waitUntilComponentAvailable($query, $timeout = 60)
    {
        $this->runWhenComponentAvailable($query, 'return true;', $timeout);
    }

    /**
     * @param string $query
     * @param int $timeout
     */
    public function waitUntilComponentRemoved($query, $timeout = 60)
    {
        $this->doRunWhenComponentRemoved($query, 'return true;', $timeout);
    }

    /**
     * @param string $query
     * @param string $fieldName
     * @param string $fieldValue
     * @param int $timeout
     *
     * @return string
     */
    public function extGridColumnWithValue($query, $fieldName, $fieldValue, $timeout = 60)
    {
        $stmt = <<<'JST'
    var grid = result[0];
        
    var index = grid.getStore().findExact("%s", "%s");
    if (-1 != index) {
        return grid.getView().getNode(index).id;
    }
JST;

        $stmt = sprintf($stmt, $fieldName, $fieldValue);

        $startTime = time();

        return WebDriverBy::id($this->doRunWhenComponentAvailable($query, $stmt, $startTime, $timeout));
    }

    /**
     * @param string $query
     * @param string $fieldName
     * @param string $fieldValue
     * @param int $timeout
     *
     * @return string
     */
    public function extDataviewColumnWithSubstringValue($query, $fieldName, $fieldValue, $timeout = 60)
    {
        $stmt = <<<'JST'
    var dataView = result[0];
        
    var index = dataView.getStore().find("%s", /%s/);
    if (-1 != index) {
        return dataView.getNode(index).id;
    }
JST;

        $stmt = sprintf($stmt, $fieldName, $fieldValue);

        $startTime = time();

        return WebDriverBy::id($this->doRunWhenComponentAvailable($query, $stmt, $startTime, $timeout));
    }

    /**
     * @param string $query
     * @param string $fieldName
     * @param string $fieldValue
     * @param int $timeout
     *
     * @return string
     */
    public function extDataviewColumnWithValue($query, $fieldName, $fieldValue, $timeout = 60)
    {
        $stmt = <<<'JST'
    var dataView = result[0];
        
    var index = dataView.getStore().findExact("%s", "%s");
    if (-1 != index) {
        return dataView.getNode(index).id;
    }
JST;

        $stmt = sprintf($stmt, $fieldName, $fieldValue);

        $startTime = time();

        $id = WebDriverBy::id($this->doRunWhenComponentAvailable($query, $stmt, $startTime, $timeout));

        sleep(1);

        return $id;
    }

    private function doRunWhenComponentAvailable($query, $stmt, $startTime, $timeout = 60, $js = null)
    {
        // If we return a boolean value from a function then we will get
        // "java.lang.Boolean cannot be cast to java.lang.String" exception by Selenium, so to address this issue
        // we are returning a 'false' as a string instead

        // maybe can be added later:
        //
        $js = $js ?? <<<'JST'
%function_name% = function () {
    var result = [];
    var components = Ext.ComponentQuery.query("%query%");
    Ext.each(components, function(component) {
        if (component.isVisible(true) && 
            (typeof component.getStore !== 'function' || !component.getStore().isLoading()) &&
            (typeof component.loadMask === 'undefined' || !component.loadMask || typeof component.loadMask.isVisible !== 'function' || !component.loadMask.isVisible()) &&
            (!Ext.ComponentQuery.query('component[loadMask]{hasOwnProperty("loadMask")}{loadMask!=undefined}{loadMask!=null}{loadMask.isVisible}{loadMask.isVisible()} component[id='+component.getId()+']').length)) {
            result.push(component);
        }
    });
    
    if (result.length > 0) {
        var firstCmp = result[0];
        
        %stmt%
    }
    
    return 'false';
};
JST;
        $functionName = 'edq_'.uniqid();

        $js = str_replace(
            ['%function_name%', '%query%', '%stmt%'],
            [$functionName, addslashes($query), $stmt],
            $js
        );



        // publishing a function once and later just invoking it instead of re-declaring it each time
        $this->driver->executeScript($js);

        while (true) {
            $value = $this->driver->executeScript("return window.$functionName();"); // invoking previously published function

            if ('false' !== $value) {
                // function is no longer needed so we are removing it from the browser
                $this->driver->executeScript("delete window.$functionName;");

                return $value;
            }

            if ((time() - $startTime) > $timeout) {
                throw new NoElementFoundException(sprintf(
                    'Unable to locate element with ExtJs query "%s" (waited for %d seconds).', $query, $timeout
                ));
            }
        }
    }

    private function doRunWhenComponentRemoved($query, $stmt, $startTime, $timeout = 60)
    {
        // If we return a boolean value from a function then we will get
        // "java.lang.Boolean cannot be cast to java.lang.String" exception by Selenium, so to address this issue
        // we are returning a 'false' as a string instead
        $js = <<<'JST'
%function_name% = function () {
    var result = [];
    var components = Ext.ComponentQuery.query("%query%");
    Ext.each(components, function(component) {
        if (component.isVisible(true)) {
            result.push(component);
        }
    });

    if (result.length == 0) {
        %stmt%
    }

    return 'false';
};
JST;
        $functionName = 'edq_'.uniqid();

        $js = str_replace(
            ['%function_name%', '%query%', '%stmt%'],
            [$functionName, addslashes($query), $stmt],
            $js
        );



        // publishing a function once and later just invoking it instead of re-declaring it each time
        $this->driver->executeScript($js);

        while (true) {
            $value = $this->driver->executeScript("return window.$functionName();"); // invoking previously published function

            if ('false' !== $value) {
                // function is no longer needed so we are removing it from the browser
                $this->driver->executeScript("delete window.$functionName;");

                return $value;
            }

            if ((time() - $startTime) > $timeout) {
                throw new NoElementFoundException(sprintf(
                    'Unable to locate element with ExtJs query "%s" (waited for %d seconds).', $query, $timeout
                ));
            }
        }
    }

    /**
     * Searches for component with specific tid
     *
     * @param string $query
     */
    public function extComponentIsNotVisible($query)
    {
        $js = <<<'JST'
%function_name% = function () {
    var components = Ext.ComponentQuery.query("%query%");
    var notVisible = 'true';
    Ext.each(components, function(cmp){
        if (cmp && cmp.isVisible()) {
            notVisible = 'false';
        }
    });
    return notVisible;
};
JST;

        $functionName = 'edq_'.uniqid();

        $js = str_replace(
            ['%function_name%', '%query%'],
            [$functionName, addslashes($query)],
            $js
        );

        // publishing a function once and later just invoking it instead of re-declaring it each time
        $this->driver->executeScript($js);

        $startTime = time();
        $timeout = 30;

        while (true) {
            $value = $this->driver->executeScript("return window.$functionName();"); // invoking previously published function

            if ('false' !== $value) {
                // function is no longer needed so we are removing it from the browser
                $this->driver->executeScript("delete window.$functionName;");

                return $value;
            }

            if ((time() - $startTime) > $timeout) {
                throw new NoElementFoundException(sprintf(
                    'Element with ExtJs query "%s" still displayed (waited for %d seconds).', $query, $timeout
                ));
            }
        }
    }

    public function executeScript($script, array $arguments = [])
    {
        if (!$this->driver instanceof JavaScriptExecutor) {
            throw new UnsupportedOperationException(
                'driver does not implement JavaScriptExecutor'
            );
        }

        $this->dispatch('beforeScript', $script, $this);

        try {
            $result = $this->driver->executeScript($script, $arguments);
        } catch (WebDriverException $exception) {
            $this->dispatchOnException($exception);
            throw $exception;
        }

        $this->dispatch('afterScript', $script, $this);

        return $result;
    }
}
