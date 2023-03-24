<?php

namespace Drupal\element_embed\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\editor\EditorInterface;
use Drupal\element_embed\ConfigurableEmbeddableRenderElementInterface;
use Drupal\element_embed\EmbeddableElementManager;
use Drupal\embed\EmbedButtonInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to embed URLs.
 */
class ElementEmbedDialog extends FormBase {

  /**
   * The element info plugin manager.
   *
   * @var \Drupal\element_embed\EmbeddableElementManager
   */
  protected $elementManager;

  /**
   * Constructs a ElementEmbedDialog object.
   *
   * @param \Drupal\element_embed\EmbeddableElementManager $element_manager
   *   The embeddable element info plugin manager.
   */
  public function __construct(EmbeddableElementManager $element_manager) {
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.embeddable_element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'element_embed_dialog';
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\editor\EditorInterface $editor
   *   The editor to which this dialog corresponds.
   * @param \Drupal\embed\EmbedButtonInterface $embed_button
   *   The URL button to which this dialog corresponds.
   */
  public function buildForm(array $form, FormStateInterface $form_state, EditorInterface $editor = NULL, EmbedButtonInterface $embed_button = NULL) {
    $values = $form_state->getValues();
    $input = $form_state->getUserInput();
    // Set URL button element in form state, so that it can be used later in
    // validateForm() function.
    $form_state->set('embed_button', $embed_button);
    $form_state->set('editor', $editor);
    // Initialize URL element with form attributes, if present.
    $render_element = $values['attributes'] ?? [];
    $render_element += $input['attributes'] ?? [];
    // The default values are set directly from \Drupal::request()->request,
    // provided by the editor plugin opening the dialog.
    if (!$form_state->get('render_element')) {
      $form_state->set('render_element', $input['editor_object'] ?? []);
    }
    $render_element += $form_state->get('render_element');
    $render_element += [
      'data-element-type' => $embed_button->getTypeSetting('element_type'),
      'data-element-settings' => [],
    ];

    if (is_string($render_element['data-element-settings'])) {
      $render_element['data-element-settings'] = Json::decode($render_element['data-element-settings']) ?: [];
    }

    $form_state->set('render_element', $render_element);

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#prefix'] = '<div id="element-embed-dialog-form">';
    $form['#suffix'] = '</div>';

    $form['attributes']['data-element-type'] = [
      '#type' => 'value',
      '#value' => $render_element['data-element-type'],
    ];

    if (empty($render_element['data-element-type'])) {
      $form['attributes']['data-element-type'] = [
        '#type' => 'select',
        '#title' => $this->t('Element type'),
        '#options' => $this->elementManager->getElementOptions(),
        '#default_value' => $render_element['data-element-type'],
        '#empty_value' => '',
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::updateElementSettings',
        ],
      ];
    }
    else {
      $form['attributes']['data-element-type'] = [
        '#type' => 'value',
        '#value' => $render_element['data-element-type'],
      ];
    }

    $form['attributes']['data-element-settings'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => [
        'id' => 'element-embed-element-settings',
      ],
      '#required' => TRUE,
    ];

    if (!empty($render_element['data-element-type']) && $this->elementManager->isConfigurable($render_element['data-element-type'])) {
      $form['attributes']['data-element-settings'] += $this->elementManager->getForm($render_element['data-element-type'], $render_element['data-element-settings'], $form['attributes']['data-element-settings']);
    }

    $form['attributes']['data-embed-button'] = [
      '#type' => 'value',
      '#value' => $embed_button->id(),
    ];
    $form['attributes']['data-entity-label'] = [
      '#type' => 'value',
      '#value' => $embed_button->label(),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Embed'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitForm',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * Ajax callback to update the form fields which depend on element type.
   *
   * @param array $form
   *   The build form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response with updated options for the element type.
   */
  public function updateElementSettings(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand(
      '#element-embed-element-settings',
      $form['attributes']['data-element-settings']
    ));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $configuration = $form_state->getValue(['attributes', 'data-element-settings']);
    $instance = $this->elementManager->createInstance($form_state->getValue(['attributes', 'data-element-type']), $configuration);
    if (method_exists($instance, 'validateForm')) {
      $subform_state = SubformState::createForSubform($form['attributes']['data-element-settings'], $form, $form_state);
      $instance::validateForm($form['attributes']['data-element-settings'], $form_state, $configuration);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $values = $form_state->getValues();
    // Display errors in form, if any.
    if ($form_state->hasAnyErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#element-embed-dialog-form', $form));
    }
    else {
      // Serialize entity embed settings to JSON string.
      if (!empty($values['attributes']['data-element-settings'])) {
        $values['attributes']['data-element-settings'] = Json::encode($values['attributes']['data-element-settings']);
      }

      // Allow other modules to alter the values before getting submitted to the
      // WYSIWYG.
      \Drupal::moduleHandler()->alter('element_embed_values', $values);

      $response->addCommand(new EditorDialogSave($values));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }
}
