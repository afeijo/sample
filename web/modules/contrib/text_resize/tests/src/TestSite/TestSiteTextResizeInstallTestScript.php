<?php

namespace Drupal\Tests\text_resizer\TestSite;

use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\TestSite\TestSetupInterface;

/**
 * Sets up a text resize block.
 */
class TestSiteTextResizeInstallTestScript implements TestSetupInterface {

  use BlockCreationTrait;
  use RandomGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Install text resize module.
    \Drupal::service('module_installer')->install(['block', 'text_resize']);

    // Place a text resize block.
    $this->placeBlock('text_resize_block', ['id' => 'text_resize']);
  }

}
