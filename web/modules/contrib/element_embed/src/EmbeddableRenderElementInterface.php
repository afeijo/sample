<?php

namespace Drupal\element_embed;

use Drupal\Core\Render\Element\ElementInterface;

/**
 * Defines an interface for embeddable render elements.
 */
interface EmbeddableRenderElementInterface extends ElementInterface {

  /**
   * Builds a render array for this element.
   *
   * @param array $configuration
   *   The configuration values.
   *
   * @return array
   *   The render array.
   */
  public static function getElement(array $configuration);

}
