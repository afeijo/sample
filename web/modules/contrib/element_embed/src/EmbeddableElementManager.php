<?php

namespace Drupal\element_embed;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\element_embed\Annotation\EmbeddableRenderElement;

class EmbeddableElementManager extends DefaultPluginManager {

  /**
   * Stores the available element information.
   *
   * @var array
   */
  protected $elementInfo;

  /**
   * Constructs a ElementInfoManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $this->setCacheBackend($cache_backend, 'embeddable_element_info', ['element_info_build', 'rendered']);
    parent::__construct('Element', $namespaces, $module_handler, EmbeddableRenderElementInterface::class, EmbeddableRenderElement::class);
  }

  /**
   * Get the list of available embeddable options.
   *
   * @return array
   *   The options for use in a select list.
   */
  public function getElementOptions() {
    $options = [];
    $definitions = $this->getDefinitions();
    foreach ($definitions as $key => $definition) {
      $options[$key] = $definition['label'];
    }
    return $options;
  }

  public function isConfigurable($element_type) {
    $definition = $this->getDefinition($element_type);
    return is_subclass_of($definition['class'], ConfigurableEmbeddableRenderElementInterface::class);
  }

  public function __call($name, $arguments) {
    $element_type = array_shift($arguments);
    $configuration = array_shift($arguments);
    $instance = $this->createInstance($element_type, $configuration);
    call_user_func_array([$instance, $name], $arguments);
  }

  public function getForm($element_type, array $configuration, array $form) {
    return $this->createInstance($element_type, $configuration)::getForm($form, $configuration);
  }

  public function getElement($element_type, array $configuration) {
    return $this->createInstance($element_type, $configuration)::getElement($configuration);
  }

}
