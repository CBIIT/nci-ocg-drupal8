<?php

namespace Drupal\Tests\lb_everywhere\FunctionalJavascript;

use Drupal\Component\Utility\UrlHelper;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the Layout Builder Everywhere UI.
 *
 * @group lb_everywhere
 */
class LBEverywhereTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lb_everywhere',
    'test_page_test',
  ];

  /**
   * Tests taking over a specific region.
   */
  public function testRegions() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->container->get('theme_installer')->install(['test_layout_theme']);

    $this->drupalLogin($this->createUser([
      'administer blocks',
    ]));

    $this->drupalPlaceBlock('system_powered_by_block', [
      'region' => 'header',
      'label' => 'Original block',
      'label_display' => TRUE,
    ]);

    $this->drupalGet('test-page');
    $assert_session->pageTextContains('Powered by Drupal');
    $assert_session->pageTextContains('Original block');

    $this->drupalGet('admin/structure/block');
    $page->pressButton('Start using Layout Builder for Header');
    $assert_session->buttonNotExists('Start using Layout Builder for Header');
    $assert_session->buttonExists('Stop using Layout Builder for Header');
    $assert_session->pageTextContains('The Header region is using Layout Builder');
    $page->pressButton('Start using Layout Builder for Footer');

    // Log in as a user with no permissions.
    $this->drupalLogin($this->createUser());

    $this->drupalGet('test-page');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->linkNotExists('Layout');
    $this->drupalGet('test-page', ['query' => ['mode' => 'layout']]);
    $assert_session->linkNotExists('Enable Layout Builder for the Header region');
    $assert_session->linkNotExists('Enable Layout Builder for the Footer region');
    $this->drupalGet('test-page', ['query' => ['mode' => 'layout', 'region' => 'header']]);
    $assert_session->linkNotExists('Add section');

    // Log in as a user with correct permissions.
    $this->drupalLogin($this->createUser([
      'configure any layout',
      'access toolbar',
    ]));

    $this->drupalGet('test-page');
    $assert_session->assertWaitOnAjaxRequest();

    $assert_session->pageTextNotContains('Powered by Drupal');
    $assert_session->pageTextNotContains('Original block');

    $page->clickLink('Layout');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->linkExists('Enable Layout Builder for the Header region');
    $assert_session->linkExists('Enable Layout Builder for the Footer region');
    $assert_session->elementExists('css', '#toolbar-item-lb-mode[data-toolbar-mode-active]');

    $page->clickLink('Layout');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->linkNotExists('Enable Layout Builder for the Header region');
    $assert_session->linkNotExists('Enable Layout Builder for the Footer region');
    $assert_session->elementNotExists('css', '#toolbar-item-lb-mode[data-toolbar-mode-active]');

    $page->clickLink('Layout');
    $this->assertLayoutMode();
    $assert_session->elementExists('css', '.region__select-mode')->click();

    $assert_session->linkNotExists('Enable Layout Builder for the Footer region');
    $assert_session->elementExists('css', '.region-mode-overlay');
    $assert_session->elementExists('css', '#toolbar-item-lb-mode-tray.is-active');

    $page->clickLink('Add section');
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('One column');
    $assert_session->assertWaitOnAjaxRequest();
    $page->pressButton('Add section');
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('Add block');
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('Powered by Drupal');
    $assert_session->assertWaitOnAjaxRequest();
    $page->fillField('settings[label]', 'New block');
    $page->checkField('settings[label_display]');
    $page->pressButton('Add block');
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('Save layout');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->addressEquals('test-page');
    $this->assertNotLayoutMode();

    $assert_session->pageTextContains('New block');
    $assert_session->pageTextNotContains('Original block');
    $assert_session->pageTextContains('Powered by Drupal');

    $this->drupalLogin($this->createUser([
      'administer blocks',
    ]));

    $this->drupalGet('admin/structure/block/list/test_layout_theme');
    $page->pressButton('Start using Layout Builder for Left');

    $this->drupalGet('admin/structure/block');
    $page->pressButton('Stop using Layout Builder for Header');

    $this->drupalGet('test-page');
    $assert_session->pageTextNotContains('New block');
    $assert_session->pageTextContains('Original block');
    $assert_session->pageTextContains('Powered by Drupal');
  }

  /**
   * Asserts that the site is in Layout mode.
   */
  private function assertLayoutMode() {
    $this->assertTrue($this->inLayoutMode(), 'In layout mode');
  }

  /**
   * Asserts that the site is not in Layout mode.
   */
  private function assertNotLayoutMode() {
    $this->assertFalse($this->inLayoutMode(), 'Not in layout mode');
  }

  /**
   * Indicates if the site is in Layout mode.
   *
   * @return bool
   *   TRUE if the site is in Layout mode, FALSE otherwise.
   */
  private function inLayoutMode() {
    $parts = UrlHelper::parse($this->getSession()->getCurrentUrl());
    return !empty($parts['query']['mode']) && $parts['query']['mode'] === 'layout';
  }

}
