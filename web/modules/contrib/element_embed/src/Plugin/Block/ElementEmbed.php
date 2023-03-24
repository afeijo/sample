<?php

namespace Drupal\element_embed\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\element_embed\EmbeddableElementManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for a block to output an embeddable render element.
 *
 * When using this as a parent class, you must define the render element ID
 * in the block annotation using element_type.
 */
abstract class ElementEmbed extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The embeddable element manager.
   *
   * @var \Drupal\element_embed\EmbeddableElementManager
   */
  protected EmbeddableElementManager $elementManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->elementManager = $container->get('plugin.manager.embeddable_element');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['element_type'] = $this->pluginDefinition['element_type'] ?? '';
    $configuration['element_settings'] = [];
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = $this->elementManager->getElement($this->configuration['element_type'], $this->configuration['element_settings']);

    // Because we use $build['#attributes'] we need to add an extra array layer
    // to our render array to prevent the attributes from being moved to the
    // block templates instead of being available in our component template.
    // @see https://www.drupal.org/i/3020876
    // @see \Drupal\block\BlockViewBuilder::preRender()
    // @see \Drupal\layout_builder\EventSubscriber\BlockComponentRenderArray::onBuildRender()
    return [$build];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form, $form_state);

    $element_id = $this->configuration['element_type'];
    $form['element_type'] = [
      '#type' => 'value',
      '#value' => $element_id,
    ];

    if ($this->elementManager->isConfigurable($element_id)) {
      $form['element_settings'] = $this->elementManager->getForm($element_id, $this->configuration['element_settings'], []);
    }
    else {
      $form['element_settings'] = [
        '#type' => 'value',
        '#value' => [],
      ];
    }


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['element_settings'] = $form_state->getValue(['element_settings']);
  }

}
