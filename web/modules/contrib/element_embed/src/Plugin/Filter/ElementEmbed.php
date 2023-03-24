<?php

namespace Drupal\element_embed\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\element_embed\EmbeddableElementManager;
use Drupal\embed\DomHelperTrait;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a filter to display embedded entities based on data attributes.
 *
 * @Filter(
 *   id = "element_embed",
 *   title = @Translation("Embedded render elements"),
 *   description = @Translation("Embeds elements using data attributes: data-entity-type, data-entity-uuid, and data-view-mode. Should usually run as the last filter, since it does not contain user input."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = 100,
 * )
 */
class ElementEmbed extends FilterBase implements ContainerFactoryPluginInterface {

  use DomHelperTrait;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The element info plugin manager.
   *
   * @var \Drupal\element_embed\EmbeddableElementManager
   */
  protected $elementManager;

  /**
   * Constructs a ElementEmbed object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\element_embed\EmbeddableElementManager $element_manager
   *   The embeddable element info plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, EmbeddableElementManager $element_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('plugin.manager.embeddable_element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'drupal-element') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//drupal-element[@data-element-type and @data-element-settings]') as $node) {
        /** @var \DOMElement $node */
        $element_output = '';

        try {
          $data = $this->getNodeAttributesAsArray($node);
          $build = $this->elementManager->getElement($data['data-element-type'], $data['data-element-settings']);
          $element_output = $this->renderer->executeInRenderContext(new RenderContext(), function () use (&$build) {
            return $this->renderer->render($build);
          });
          $result = $result->merge(BubbleableMetadata::createFromRenderArray($build));
        }
        catch (\Exception $exception) {
          watchdog_exception('element_embed', $exception);
        }

        $this->replaceNodeContent($node, $element_output);
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('You can embed render elements.');
  }


}
