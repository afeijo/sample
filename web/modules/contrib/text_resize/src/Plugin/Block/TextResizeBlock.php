<?php

namespace Drupal\text_resize\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a text resize block.
 *
 * @Block(
 *   id = "text_resize_block",
 *   admin_label = @Translation("Text Resize"),
 * )
 */
class TextResizeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $access = $this->blockAccess($account);
    return $return_as_object ? $access : $access->isAllowed('access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'text_resize_block',
      '#attached' => [
        'library' => ['text_resize/text_resize.resize'],
      ],
    ];
  }

}
