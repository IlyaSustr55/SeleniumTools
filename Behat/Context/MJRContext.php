<?php

namespace Modera\Component\SeleniumTools\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Facebook\WebDriver\Support\XPathEscaper;
use Modera\Component\SeleniumTools\TestHarness;
use Modera\Component\SeleniumTools\Actor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Modera\Component\SeleniumTools\PageObjects\MJRBackendPageObject;
use Modera\Component\SeleniumTools\Querying\ExtDeferredQueryHandler;
use Modera\Component\SeleniumTools\Querying\By;
use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Exception\IOException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Defines application features from the specific context.
 */
class MJRContext extends HarnessAwareContext
{
    /**
     * @Given I am on a dashboard section
     */
    public function iAmOnASection()
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) {
            $q->runWhenComponentAvailable('modera-backdashboard-dashboardpanel', 'return true;');
        });
    }

    /**
     * @Given it is programatically emulated that my session is expired
     */
    public function itIsProgramaticallyEmulatedThatMySessionIsExpired()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) {
            $admin->executeScript("ModeraFoundation.getApplication().getContainer().get('security_manager').logout(Ext.emptyFn);");

            sleep(1);
        });
    }

    /**
     * @Then I navigate to :section
     * @Then now when I try to switch a section to :section
     * @Given as a user :actor now when I try to switch a section to :section
     */
    public function iNavigateToSection($section)
    {
        $this->runActiveActor(function ($admin, $actor, MJRBackendPageObject $backend) use ($section) {
            $backend->clickMenuItemWithLabel($section);

            sleep(1);
        });
    }

    /**
     * @Then I activate :tab tab
     */
    public function iActivateTab($tab)
    {

        $this->runActiveActor(function ($admin, $actor, MJRBackendPageObject $backend) use ($tab) {
            $backend->clickTabItemWithLabel($tab);

            sleep(1);
        });
    }

    /**
     * @Then I activate left menu item :tab
     */
    public function iActivateLeftMenuItem($tab)
    {

        $this->runActiveActor(function (RemoteWebDriver $admin, Actor $actor, MJRBackendPageObject $backend, ExtDeferredQueryHandler $q) use ($tab) {
            $button = $q->extComponentDomId("mfc-touchmenu component[tid=$tab]");

            $admin->findElement($button)->click();

            sleep(2);
        });
    }

    /**
     * @Given I click on :section in Tools view
     */
    public function iClickOnSectionInToolsView($section)
    {
        $this->runActiveActor(function ($admin, $actor, MJRBackendPageObject $backend) use ($section) {
            $backend->clickToolsSectionWithLabel($section);

            sleep(1);
        });
    }

    /**
     * @Then a session expired notification modal window must be presented to a user
     */
    public function aSessionExpiredNotificationModalWindowMustBePresentedTOAUser()
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) {
            $q->runWhenComponentAvailable('window[tid=authRequiredWindow]');
        });
    }

    /**
     * @Given I click on Close button in the session expired notification window
     */
    public function iClickOnCloseButtonInTheSessionExpiredNotificationWindow()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, Actor $actor, MJRBackendPageObject $backend, ExtDeferredQueryHandler $q) {
            $button = $q->extComponentDomId('window[tid=authRequiredWindow] component[tid=closeWindowBtn]{isDisabled() == false}{isVisible() == true}');

            $admin->findElement($button)->click();

            sleep(2);
        });
    }

    /**
     * @Then the a page must be reloaded and initial login page must be displayed
     */
    public function theAPageMustBeReloadedAndInitialLoginPageMustBeDisplayed()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, Actor $actor, MJRBackendPageObject $backend, ExtDeferredQueryHandler $q) {
            Assert::assertNull($admin->executeScript("ModeraFoundation.getApplication().getContainer().get('security_context').getUser()"));
        });
    }

    /**
     * @Given I click on Ok button in the window
     */
    public function iClickCloseOkButtonInTheWindow()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, Actor $actor, MJRBackendPageObject $backend, ExtDeferredQueryHandler $q) {
            $button = $q->extComponentDomId('window[tid=authRequiredWindow] component[tid=closeWindowBtn]');

            $admin->findElement($button)->click();

            sleep(2);
        });
    }

    /**
     * @Then window is no longer displayed
     */
    public function windowIsNoLongerDisplayed()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, Actor $actor, MJRBackendPageObject $backend, ExtDeferredQueryHandler $q) {
            $this->viewIsNotVisible('authRequiredWindow');
        });
    }

    /**
     * @Given I click on Login button in the window
     */
    public function iClickOnLoginButtonInTheWindow()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, Actor $actor, MJRBackendPageObject $backend, ExtDeferredQueryHandler $q) {
            $button = $q->extComponentDomId('window[tid=authRequiredWindow] component[tid=loginBtn]');

            $admin->findElement($button)->click();

            sleep(2);
        });
    }

    /**
     * @Then view :tid is visible
     * @Then panel :tid is visible
     * @Then grid :tid is visible
     * @Then badge :tid is visible
     * @Then window :tid is visible
     *
     * @Then window :tid should stay visible
     *
     * @Then view :tid must be visible
     * @Then window :tid must be visible
     *
     * @Then view :tid must be shown
     * @Then window :tid must be shown
     *
     * @Then I can see :tid
     */
    public function viewIsVisible($tid)
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid) {
            $q->waitUntilComponentAvailable("component[tid=$tid]");

            sleep(1);
        });
    }

    /**
     * @Then confirmation window is visible
     * @Then message box is visible
     * @Then alert window is visible
     */
    public function messageboxIsVisible()
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) {
            $q->waitUntilComponentAvailable("messagebox");

            sleep(1);
        });
    }

    /**
     * @Then I see delete confirmation window
     */
    public function ISeeDeleteConfirmationWindow()
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) {
            $q->runWhenComponentAvailable("mfc-window-deleterecordconfwindow");
        });
    }

    /**
     * @Then I dont see delete confirmation window
     */
    public function IDontSeeDeleteConfirmationWindow()
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) {
            $q->extComponentIsNotVisible("mfc-window-deleterecordconfwindow");
        });
    }

    /**
     * @When view :tid is not visible
     * @When panel :tid is not visible
     * @When grid :tid is not visible
     * @When window :tid is not visible
     * @When button :tid is not visible
     *
     * @Then window :tid must be closed
     */
    public function viewIsNotVisible($tid)
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid) {
            $q->extComponentIsNotVisible("component[tid=$tid]");

            sleep(1);
        });
    }

    /**
     * @When I click :componentType with text :text
     */
    public function whenIClickComponentWithText($componentType, $text)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($componentType, $text) {
            $button = $q->extComponentDomId("{$componentType}[text=$text]");

            $admin->findElement($button)->click();

            sleep(1);
        });
    }

    /**
     * @When I click close button in :tid
     */
    public function whenIClickCloseBtn($tid)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid) {
            $button = $q->extComponentDomId("[tid=$tid] tool[type=close]");

            $admin->findElement($button)->click();

            sleep(1);
        });
    }

    /**
     * @When I click :tid
     */
    public function iClickElement($tid)
    {
        $this->iClickElementOfType('component', $tid);
    }

    /**
     * When I click "button" named "importBtn"
     *
     * @When I click :componentType named :tid
     * @When I click :componentType :tid
     */
    public function iClickElementOfType($componentType, $tid)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($componentType, $tid) {
            $expression = '';
            if ($componentType == 'button' || $componentType == 'menuitem') {
                $expression = '{isDisabled() == false}{isVisible() == true}';
            }
            $button = $q->extComponentDomId("{$componentType}[tid='$tid']$expression");
            $admin->findElements($button)[0]->click();
            sleep(1);
        });
    }

    /**
     * @When I click header :text in a grid :tid
     */
    public function iClickHeaderWithTextInAGrid($text, $tid)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($text, $tid) {
            $column = $q->extComponentDomId("grid[tid=$tid] gridcolumn[text=$text]");

            $admin->findElement($column)->click();
            sleep(1);
        });
    }

    /**
     * @Then in grid :tid row with position :position column :label must be equal to :expectedValue
     */
    public function gridColumnValueMustBe($tid, $position, $columnLabel, $expectedValue)
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid, $position, $columnLabel, $expectedValue) {
            $dataIndex = $q->runWhenComponentAvailable("grid[tid=$tid] gridcolumn[text=$columnLabel]", 'return firstCmp.dataIndex');

            Assert::assertNotNull($dataIndex);

            $givenValue = $q->runWhenComponentAvailable(
                "grid[tid=$tid]",
                "var view = firstCmp.getView(); var node = view.getNode($position); console.log(view.getRecord(node).get('$dataIndex')); return view.getRecord(node).get('$dataIndex')"
            );

            Assert::assertEquals($expectedValue, $givenValue);
        });
    }

    /**
     * @Then in grid :tid row with position :position column :label
     */
    public function aldifjs($tid, $position, $columnLabel)
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid, $position, $columnLabel) {
            $dataIndex = $q->runWhenComponentAvailable("grid[tid=$tid] gridcolumn[text=$columnLabel]", 'return firstCmp.dataIndex');

            Assert::assertNotNull($dataIndex);
        });
    }

    /**
     * @Then in grid :tid row with position :position column :label must be empty
     */
    public function gridColumnValueMustBeEmpty($tid, $position, $columnLabel)
    {
        $this->runActiveActor(function ($admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid, $position, $columnLabel) {
            $dataIndex = $q->runWhenComponentAvailable("grid[tid=$tid] gridcolumn[text=$columnLabel]", 'return firstCmp.dataIndex');

            Assert::assertNotNull($dataIndex);

            $givenValue = $q->runWhenComponentAvailable(
                "grid[tid=$tid]",
                "var view = firstCmp.getView(); var node = view.getNode($position); console.log(view.getRecord(node).get('$dataIndex')); return view.getRecord(node).get('$dataIndex')"
            );

            Assert::assertEquals("", $givenValue);
        });
    }

    /**
     * @When I replace text :text in field :tid
     * @When I replace text :text in textarea :tid
     *
     * @When I replace :text in field :tid
     * @When I replace :text in textarea :tid
     *
     * @When I replace text :text in :nth field :tid
     * @When I replace text :text in :nth textarea :tid
     *
     * @When I replace :text in :nth field :tid
     * @When I replace :text in :nth textarea :tid
     */
    public function iReplaceTextInField($text, $tid, $nth = 1)
    {
        if ($nth == 'first') {
            $nth = 1;
        } else if ($nth == 'second') {
            $nth = 2;
        } else if ($nth == 'third') {
            $nth = 3;
        } else if ($nth == 'fourth') {
            $nth = 4;
        }
        if ($text == "nothing") {
            $text = "";
        }

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($text, $tid, $nth) {
            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $js = <<<'JS'
    var fieldDomId = firstCmp.el.dom.id;

    var inputs = Ext.query("#"+fieldDomId+" input");
    if (inputs[0]) {
        if (!inputs[0].id || inputs[0].id === "") inputs[0].id = Ext.id();
        return inputs[0].id;
    }

    var editor = Ext.query("#"+fieldDomId+" textarea");
    if (editor[0]) {
        if (!editor[0].id || editor[0].id === "") editor[0].id = Ext.id();
        return editor[0].id;
    }
    
    throw "Unable to find neither 'input' nor 'textarea' for given TID.";
JS;

            $input = By::id($q->runWhenComponentAvailable("component[tid=$tid]:nth-child({$nth}n)", $js));
            $element = $admin->findElement($input);
            $element->clear();
            $element->sendKeys($text);

            sleep(1);
        });
    }

    /**
     * @When I move mouse on header :text in a grid :tid
     */
    public function iMoveMouseOnHeaderWithTextInAGrid($text, $tid)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($text, $tid) {
            $column = $q->extComponentDomId("grid[tid=$tid] gridcolumn[text=$text]");

            $admin->findElement($column);
            $admin->getMouse()->mouseMove($admin->findElement($column)->getCoordinates());
            $admin->findElement(By::xpath('//*[@id="' . $column->getValue() . '"]//div[@class="x-column-header-trigger"]'))->click();
            sleep(1);
        });
    }

    /**
     * @When I select month to :year :month
     */
    public function iSelectMonth($year, $month)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($year, $month) {

            $admin->findElements(By::xpath('//span[@class = "x-btn-wrap x-btn-split x-btn-split-right"]/span/span'))[1]->click();
            sleep(1);
            $admin->findElement(By::xpath('//a[contains(@class, "x-monthpicker-item-inner") and contains(text(), "' . $month . '")]'))->click();
            $admin->findElement(By::xpath('//a[contains(@class, "x-monthpicker-item-inner") and contains(text(), "' . $year . '")]'))->click();
            $column = $q->extComponentDomId('[text="&#160;OK&#160;"]');
            $admin->findElement($column)->click();

        });
    }

    /**
     * @When I press button Today
     */
    public function iPressButtonToday()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) {
            $column = $q->extComponentDomId('button[text="Today"]');
            $admin->findElement($column)->click();
        });
    }

    /**
     * @Then text in :tid is equal to
     */
    public function viewHasText($tid, PyStringNode $markdown)
    {
        $this->iSeeValueInField($markdown->getRaw(), $tid);
    }

    /**
     * @Then I see text :text in :tid
     * @Then I see value :text in :tid
     * @Then I see text :text in field :tid
     * @Then I see text :text in textarea :tid
     * @Then Value in field :tid must be equal to :text
     * @Then Value in textarea :tid must be equal to :text
     *
     * @Then I see :text in field :tid
     * @Then I see :text in textarea :tid
     * @Then Value in :tid must be equal to :text
     *
     * @Then I see text :text in :nth field :tid
     * @Then I see text :text in :nth textarea :tid
     * @Then Value in :nth field :tid must be equal to :text
     * @Then Value in :nth textarea :tid must be equal to :text
     *
     * @Then I see :text in :nth field :tid
     * @Then I see :text in :nth textarea :tid
     * @Then Value in :nth :tid field must be equal to :text
     * @Then Value in :nth :tid textarea must be equal to :text
     */
    public function iSeeValueInField($text, $tid, $nth = 1)
    {
        if ($nth == 'first') {
            $nth = 1;
        } else if ($nth == 'second') {
            $nth = 2;
        } else if ($nth == 'third') {
            $nth = 3;
        } else if ($nth == 'fourth') {
            $nth = 4;
        } else if ($nth == 'fifth') {
            $nth = 5;
        } else if ($nth == 'sixth') {
            $nth = 6;
        }

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($text, $tid, $nth) {

            $js = <<<'JS'
    if (firstCmp.xtype == 'combobox' || firstCmp.xtype == 'combo') {
         return firstCmp.getDisplayValue();
    } else if (firstCmp.xtype == 'panel') {
         return firstCmp.initialConfig.html.replace(/(?!(\<br\>|\<br\s\/\>))<\/?[^>]+>/g, '');
    } else if(firstCmp.xtype == 'mfc-datefield' || firstCmp.xtype == 'datefield') {
    
        var value = firstCmp.getValue();
        if (value && typeof value === 'string') {
            return value;
        }
    
        return Ext.Date.format(firstCmp.getValue(), 'l, d M Y');
    } else if(firstCmp.xtype == 'mfc-header') {
        return firstCmp.title;
    } else if (firstCmp.xtype == 'button' || firstCmp.xtype == 'splitbutton' || firstCmp.xtype == 'deal-detail-btn' || firstCmp.xtype == 'menubutton'  || firstCmp.xtype == 'menuitem') {
         return firstCmp.text;
    } else if (firstCmp.xtype == 'box') {
        if(firstCmp.html != undefined)
            return firstCmp.html;
        else return firstCmp.initialConfig.html;
    } else if (firstCmp.tid == "totalAccessoriesPrice") {
         return firstCmp.html;
    } else if (firstCmp.tid == "message") {
         return firstCmp.text.html;
    } else if (firstCmp.xtype == "tbtext") {
         return firstCmp.container.dom.innerText;
    } else {
         return firstCmp.getValue();
    }
JS;

            $value = $q->runWhenComponentAvailable("component[tid=$tid]:nth-child({$nth}n)", $js);

            $value = str_replace("\n", "", strip_tags(trim($value)));

            if (stristr($text, "TODAY_DATE")) {
                try {
                    $text = explode(" ", $text);
                    $text[1] = str_replace("(", "", $text[1]);
                    $text[1] = str_replace(")", "", $text[1]);
                    $text = str_replace($text[0], date($text[1]), $text[0]);
                } catch (\Exception $e) {
                    $text = str_replace("TODAY_DATE", date("d.m.Y"), $text[0]);
                }
            }

            if ($text != $value) var_dump($text, $value, $text == $value);
            Assert::assertEquals($text, $value);

        });
    }


    /**
     * @Then I dont see text in :type :tid
     * @Then I dont see text in :nth :type :tid
     * @Then I dont see text in :tid
     */
    public function thenIDonSeeText($type = "component", $tid, $nth = 1)
    {
        if ($nth == 'first') {
            $nth = 1;
        } else if ($nth == 'second') {
            $nth = 2;
        } else if ($nth == 'third') {
            $nth = 3;
        } else if ($nth == 'fourth') {
            $nth = 4;
        }

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($type, $tid, $nth) {

            $js = <<<'JS'
    var component = Ext.ComponentQuery.query("%type%[tid=%tid%]:nth-child(%nth%n)")[0];
    if (component.xtype == 'combobox' || component.xtype == 'combo') {
         return component.getDisplayValue();
    } else if (component.xtype == 'button') {
         return component.text;
    } else if (component.xtype == 'box') {
         return component.html;
    } else {
         return component.getValue();
    }
JS;
            $js = str_replace(['%type%', '%tid%', '%nth%'], [$type, $tid, $nth], $js);
            $value = $q->runWhenComponentAvailable("component[tid=$tid]", $js);
            if ($value != "") var_dump("", $value, "" == $value);
            Assert::assertEquals("", $value);

        });
    }

    /**
     * @When I type text :text in field :tid
     * @When I type text :text in textarea :tid
     *
     * @When I type :text in field :tid
     * @When I type :text in textarea :tid
     *
     * @When I type text :text in :nth field :tid
     * @When I type text :text in :nth textarea :tid
     *
     * @When I type :text in :nth field :tid
     * @When I type :text in :nth textarea :tid
     */
    public function iTypeTextInField($text, $tid, $nth = 1)
    {
        if ($nth == 'first') {
            $nth = 1;
        } else if ($nth == 'second') {
            $nth = 2;
        } else if ($nth == 'third') {
            $nth = 3;
        } else if ($nth == 'fourth') {
            $nth = 4;
        }

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($text, $tid, $nth) {
            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $js = <<<'JS'
    var fieldDomId = firstCmp.el.dom.id;

    var inputs = Ext.query("#"+fieldDomId+" input");
    if (inputs[0]) {
        return inputs[0].id;
    }

    var textareas = Ext.query("#"+fieldDomId+" textarea");
    if (textareas[0]) {
        return textareas[0].id;
    }

    throw "Unable to find neither 'input' nor 'textarea' for given TID.";
JS;

            $input = By::id($q->runWhenComponentAvailable("component[tid=$tid]:nth-child({$nth}n)", $js));

            $element = $admin->findElement($input);
            $element->sendKeys($text);

            sleep(1);
        });
    }

    /**
     * @When I wait for :seconds seconds
     * @When I wait :seconds seconds
     */
    public function iWaitForSeconds($seconds)
    {
        if ($seconds == 'couple') {
            $seconds = 10;
        }
        sleep($seconds);
    }

    /**
     * @When I wait a bit
     */
    public function iWaitABit()
    {
        sleep(10);
    }

    /**
     * TODO Refactor, is is pretty much copy-paste of iTypeTextInField
     *
     * @When I clear text in a field :tid
     * @When I clear text in a textarea :tid
     */
    public function iClearTextInField($tid)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid) {
            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $js = <<<'JS'
    var fieldDomId = firstCmp.el.dom.id;

    var inputs = Ext.query("#"+fieldDomId+" input");
    if (inputs[0]) {
        return inputs[0].id;
    }

    var textareas = Ext.query("#"+fieldDomId+" textarea");
    if (textareas[0]) {
        return textareas[0].id;
    }

    throw "Unable to find neither 'input' nor 'textarea' for given TID.";
JS;

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $inputEl = By::id($q->runWhenComponentAvailable("component[tid=$tid]", $js));

            $input = $admin->findElement($inputEl);
            $input->clear();
            $input->sendKeys(WebDriverKeys::UP);

            sleep(1);
        });
    }

    /**
     * @When I load url :url
     */
    public function iLoadUrl($url)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($url) {
            $admin->executeScript('window.location = "' . $url . '";');
            sleep(1);
        });
    }

    /**
     * @When I refresh the page
     */
    public function iRefreshThenPage()
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) {
            $admin->executeScript('window.location.reload();');
            sleep(1);
        });
    }

    /**
     * @Then I see a badge with text :text
     */
    public function iSeeBadgeWithText($text)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($text) {
            $admin->wait()->until(WebDriverExpectedCondition::visibilityOfAnyElementLocated(WebDriverBy::xpath('//div[contains(@class, "tag") and contains(text(), "'.$text.'")]')));
        });
    }

    /**
     * @Then I do not see a badge with text :text
     */
    public function iDoNotSeeBadgeWithText($text)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($text) {
            $admin->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::xpath('//div[contains(@class, "tag") and contains(text(), "'.$text.'")]')));
        });
    }

    /**
     * @Then I wait for a piece of text :text to be visible
     */
    public function iWaitPieceOfTextToBeVisible($text)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($text) {
            $admin->wait(300, 500)->until(
                WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('body'), $text)
            );
        });
    }

    /**
     * @Then I see a piece of text :text
     */
    public function iSeePieceOfText($text)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($text) {
            $admin->wait()->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::tagName('body'), $text));
        });
    }

    /**
     * @Then I see a piece of text matched to :pattern
     */
    public function iSeePieceOfTextMatchedTo($pattern)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($pattern) {

            $pattern = preg_replace_callback('/([^*])/', function ($m) {
                return preg_quote($m[1], "/");
            }, $pattern);
            $pattern = str_replace('*', '.*', $pattern);
            Assert::assertTrue((bool)preg_match('/' . $pattern . '/i', $admin->getPageSource()));
        });
    }

    /**
     * @Then I do not see a piece of text matched to :pattern
     */
    public function iDoNotSeePieceOfTextMatchedTo($pattern)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($pattern) {

            $pattern = preg_replace_callback('/([^*])/', function ($m) {
                return preg_quote($m[1], "/");
            }, $pattern);
            $pattern = str_replace('*', '.*', $pattern);

            Assert::assertFalse((bool)preg_match('/' . $pattern . '/i', $admin->getPageSource()));
        });
    }

    /**
     * @Then I do not see a piece of text :text
     */
    public function iDoNotSeePieceOfText($text)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($text) {
            Assert::assertNotContains($text, $admin->getPageSource());
        });
    }

    /**
     * @When I authenticate as :username with password :password
     * @When I login as :username with password :password
     */
    public function iAuthenticateAs($username, $password)
    {
        $this->switchActor($username);
        $this->runActiveActor(function ($admin, $actor, MJRBackendPageObject $backend) use ($username, $password) {
            $backend->login($username, $password);

            sleep(1);
        });
    }

    /**
     * @Then I am not authenticated
     * @Then I must not be authenticated
     */
    public function iAmNotAuthenticated()
    {
        $this->isActorAuthenticated(function ($isAuthenticated) {
            Assert::assertEquals(false, $isAuthenticated);
        });
    }

    /**
     * @param $isAuthenticatedCallback
     */
    private function isActorAuthenticated($isAuthenticatedCallback)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin) use ($isAuthenticatedCallback) {
            $js = <<<JS
var done = arguments[arguments.length-1],
    sm = ModeraFoundation.getApplication().getContainer().get('security_manager');

sm.isAuthenticated(function(result) {
    done(result.success);
});
JS;

            $admin->manage()->timeouts()->setScriptTimeout(5);

            $isAuthenticatedCallback($admin->executeAsyncScript($js));
        });
    }

    /**
     * @Then I am successfully authenticated
     * @Then I must be successfully authenticated
     */
    public function iAmSuccessfullyAuthenticated()
    {
        $this->isActorAuthenticated(function ($isAuthenticated) {
            Assert::assertEquals(true, $isAuthenticated);
        });
    }

    /**
     * @When we switch back to :username
     * @When session is switched to :username
     */
    public function sessionIsSwitchedTo($username)
    {
        $this->switchActor($username);
    }

    /**
     * @When I expand menu for button :tid
     */
    public function iExpandMenuForButton($tid)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid) {
            $js = <<<JS
var button = firstCmp;

return [button.getWidth(), button.getHeight(), button.el.dom.id];
JS;

            $result = $q->runWhenComponentAvailable("button[tid=$tid] ", $js);

            list($width, $height, $domId) = $result;

            $button = $admin->findElement(By::id($domId));

            $admin->action()
                ->moveToElement($button, round($width/2-5))
                ->click()
                ->perform();
        });
    }

    /**
     * @When I click :button button in confirmation window
     * @When I click :button button in alert window
     */
    public function IClickButtonInConfirmationWindow($button)
    {

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($button) {

            $button = $q->extComponentDomId("messagebox button[itemId={$button}]");

            $admin->findElement($button)->click();

            sleep(2);

        });
    }

    /**
     * @When I choose date :date in field :tid
     */
    public function iChooseDateInField($date, $tid)
    {

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid, $date) {
            $js = <<<JS
var dateField = firstCmp;
dateField.setValue(new Date('%expectedValue%'));
return true;
JS;
            $js = str_replace(['%expectedValue%'], [$date], $js);

            $q->runWhenComponentAvailable("component[tid=$tid]", $js);

        });
    }


    /**
     * @When I select option :option and option :option2 in combo :tid
     * @When I select option :option and option :option2 in :nth combo :tid
     */
    public function iSelectOptionsInCombo($option, $option2, $tid, $nth = 1)
    {

        if ($nth == 'first') {
            $nth = 1;
        } else if ($nth == 'second') {
            $nth = 2;
        } else if ($nth == 'third') {
            $nth = 3;
        } else if ($nth == 'fourth') {
            $nth = 4;
        }

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid, $option, $option2, $nth) {
            $js = <<<JS
var combo = firstCmp;
var store = combo.getStore();
var record = store.findRecord(combo.displayField, '%expectedValue%');
if (!record) {
    throw "Unable to find a record where option value is equal to '%expectedValue%'." ;
}
var record2 = store.findRecord(combo.displayField, '%expectedValue2%');
if (!record2) {
    throw "Unable to find a record where option value is equal to '%expectedValue2%'." ;
}
combo.setValue([record, record2]);
return true;
JS;
            $js = str_replace(['%expectedValue%', '%expectedValue2%'], [$option, $option2], $js);

            $q->runWhenComponentAvailable("combo[tid=$tid]:nth-child({$nth}n)", $js);

        });
    }

    /**
     * @When I select option :option in combo :tid
     * @When I select option :option in :nth combo :tid
     */
    public function iSelect2OptionsInCombo($option, $tid, $nth = 1)
    {

        if ($nth == 'first') {
            $nth = 1;
        } else if ($nth == 'second') {
            $nth = 2;
        } else if ($nth == 'third') {
            $nth = 3;
        } else if ($nth == 'fourth') {
            $nth = 4;
        }

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid, $option, $nth) {
            $js = <<<JS
var combo = firstCmp;
var store = combo.getStore();

if (store.isLoading()) {
    return 'false';
}

var record = store.findRecord(combo.displayField, '%expectedValue%');
if (!record) {
    return 'false';
}
combo.setValue(record);

return true;
JS;
            $js = str_replace(['%expectedValue%'], [$option], $js);

            $q->runWhenComponentAvailable("combo[tid=$tid]:nth-child({$nth}n)", $js);

        });
    }

    /**
     * @When I select radio option :option
     */
    public function iSelectRadioOption($option)
    {

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($option) {

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $js = <<<'JS'
    var fieldDomId = firstCmp.el.dom.id;

    var inputs = Ext.query("#"+fieldDomId+" input");
    if (inputs[0]) {
        return inputs[0].id;
    }

    throw "Unable to find 'input' for given TID.";
JS;

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $inputEl = By::id($q->runWhenComponentAvailable("radiofield[tid=$option]", $js));

            $admin->findElement($inputEl)->click();

            sleep(1);

        });
    }


    /**
     * @When in combobox :tid I click item with text :text
     */
    public function abcd($tid, $text)
    {

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid, $text) {

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $js = <<<'JS'
    var fieldDomId = firstCmp.el.dom.id;
    for(var i = 0; i < firstCmp.picker.all.elements.length; i++){
        if(firstCmp.picker.all.elements[i].innerText === '%expectedValue%')
            firstCmp.picker.all.elements[i].click();
    }
    return;
JS;

            $combobox = $q->extComponentDomId("combobox[tid=" . $tid . "]");

            $admin->findElement($combobox)->click();
            sleep(2);
            $js = str_replace(['%expectedValue%'], [$text], $js);
            $inputEl = By::id($q->runWhenComponentAvailable("combobox[tid=" . $tid . "]", $js));
            $admin->findElement($combobox)->click();
        });
    }

    /**
     * @When I select checkbox :option
     */
    public function iSelectCheckbox($option)
    {

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($option) {

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $js = <<<'JS'
    var fieldDomId = firstCmp.el.dom.id;

    var inputs = Ext.query("#"+fieldDomId+" input");
    if (inputs[0]) {
        return inputs[0].id;
    }

    throw "Unable to find 'input' for given TID.";
JS;

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $inputEl = By::id($q->runWhenComponentAvailable("checkbox[tid=$option]", $js));

            $admin->findElement($inputEl)->click();

            sleep(1);

        });
    }

    /**
     * @When I click on view :option
     */
    public function iClickView($option)
    {

        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($option) {

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $js = <<<'JS'
    var fieldDomId = firstCmp.el.dom.id;

    var inputs = Ext.query("#"+fieldDomId+" input");
    if (inputs[0]) {
        return inputs[0].id;
    }

    throw "Unable to find 'input' for given TID.";
JS;

            // We cannot simply query by $tid, because it returns HTML <table> element instead of <input> that we need
            $inputEl = By::id($q->runWhenComponentAvailable("[tid=$option]", $js));

            $admin->findElement($inputEl)->click();

            sleep(1);

        });
    }

    /**
     * @When I expand options for select :tid
     */
    public function iExpandOptionsForSelect($tid)
    {
        $this->runActiveActor(function (RemoteWebDriver $admin, $actor, $backend, ExtDeferredQueryHandler $q) use ($tid) {
            $js = <<<JS
var button = firstCmp;

return [button.getWidth(), button.getHeight(), button.el.dom.id];
JS;

            $result = $q->runWhenComponentAvailable("combobox[tid=$tid] ", $js);

            list($width, $height, $domId) = $result;

            $button = $admin->findElement(By::id($domId));

            $admin->action()
                ->moveToElement($button, $width - 5, 5)
                ->click()
                ->perform();
        });
    }
}

