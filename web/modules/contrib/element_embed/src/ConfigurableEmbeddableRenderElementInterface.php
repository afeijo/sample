<?php

namespace Drupal\element_embed;

/**
 * Defines an interface for transformable Design System elements.
 */
interface ConfigurableEmbeddableRenderElementInterface extends EmbeddableRenderElementInterface {

  /**
   * Builds a form for this element element.
   *
   * @param array $form
   *   An associative array containing the initial structure of the element
   *   form.
   * @param array $configuration
   *   The configuration values.
   *
   * @return array
   *   The form elements to be embedded in a form.
   */
  public static function getForm(array $form, array $configuration);

}
