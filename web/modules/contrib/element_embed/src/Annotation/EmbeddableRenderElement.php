<?php

namespace Drupal\element_embed\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an embeddable render element annotation object.
 *
 * @Annotation
 */
class EmbeddableRenderElement extends Plugin {

  /**
   * The render element ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the render element.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  protected $label;

}
