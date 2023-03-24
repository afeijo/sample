<?php

namespace Drupal\element_embed\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\embed\EmbedCKEditorPluginBase;

/**
 * Defines the "drupalelement" plugin.
 *
 * @CKEditorPlugin(
 *   id = "drupalelement",
 *   label = @Translation("Render Element"),
 *   embed_type_id = "element_embed",
 *   required_filter_plugin_id = "element_embed",
 * )
 */
class ElementEmbed extends EmbedCKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->getModulePath('element_embed') . '/js/plugins/drupalelement/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'DrupalElement_dialogTitleAdd' => t('Insert element'),
      'DrupalElement_dialogTitleEdit' => t('Edit element'),
      'DrupalElement_buttons' => $this->getButtons(),
      'drupalEmbed_previewCsrfToken' => \Drupal::csrfToken()->get('X-Drupal-EmbedPreview-CSRF-Token'),
    ];
  }

}
