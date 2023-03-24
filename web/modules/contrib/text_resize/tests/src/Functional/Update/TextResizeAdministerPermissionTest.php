<?php

namespace Drupal\Tests\text_resize\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;

/**
 * Tests the 'administer text_resize' permission update path.
 *
 * @group text_resize
 * @group legacy
 */
class TextResizeAdministerPermissionTest extends UpdatePathTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-9.3.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/8.x-1.2/fixture.php',
    ];
  }

  /**
   * Tests the 'administer text resizing' permission is granted.
   */
  public function testAdministerPermission() {
    // Add a new 'Junior Admin' role with the legacy permission we care about.
    $this->createRole(['access administration pages'], 'junior_admin', 'Junior Admin');

    $role = Role::load('junior_admin');
    $this->assertTrue($role->hasPermission('access administration pages'), 'Junior Admin role has legacy permission.');
    $this->assertFalse($role->hasPermission('administer text_resize'), 'Junior Admin role does not have the new permission.');

    $this->runUpdates();

    $role = Role::load('junior_admin');
    $this->assertTrue($role->hasPermission('access administration pages'), 'Junior Admin role still has the legacy permission.');
    $this->assertTrue($role->hasPermission('administer text_resize'), 'Junior Admin role now has the new permission.');
  }

}
