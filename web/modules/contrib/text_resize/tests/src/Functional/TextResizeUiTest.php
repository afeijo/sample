<?php

namespace Drupal\Tests\text_resize\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests text resize UI.
 *
 * @group text_resize
 */
class TextResizeUiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'text_resize'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests configuration access.
   */
  public function testConfigurationAccess() {
    $assert_session = $this->assertSession();

    // Ensure that the configuration page can not be accessed.
    $this->drupalGet('admin/config/user-interface/text_resize');
    $assert_session->statusCodeEquals(403);

    // Login as an admin user to the site.
    $this->drupalLogin($this->drupalCreateUser(['administer text_resize']));
    $this->drupalGet('admin/config/user-interface/text_resize');
    $assert_session->statusCodeEquals(200);
  }

  /**
   * Tests configuration changes take effect immediately in the DOM.
   *
   * @see https://www.drupal.org/node/3293246
   */
  public function testConfigurationChanges() {
    $assert_session = $this->assertSession();

    $this->drupalPlaceBlock('text_resize_block');
    $assert_session->elementNotExists('css', '#text_resize_reset');

    $this->drupalLogin($this->drupalCreateUser([], NULL, TRUE));
    $this->drupalGet('admin/config/user-interface/text_resize');
    $this->submitForm(['text_resize_reset_button' => TRUE], 'Save configuration');

    $assert_session->elementExists('css', '#text_resize_reset');
  }

}
