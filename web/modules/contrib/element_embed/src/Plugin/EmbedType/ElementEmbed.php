<?php

namespace Drupal\element_embed\Plugin\EmbedType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\embed\EmbedType\EmbedTypeBase;

/**
 * Principal Design System Element embed type.
 *
 * @EmbedType(
 *   id = "element_embed",
 *   label = @Translation("Render Element"),
 * )
 */
class ElementEmbed extends EmbedTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'element_type' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['element_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Element'),
      '#options' => \Drupal::service('plugin.manager.embeddable_element')->getElementOptions(),
      '#default_value' => $this->configuration['element_type'],
      '#empty_option' => $this->t('- Allow user to choose -'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['element_type'] = $form_state->getValue('element_type');
  }

}
