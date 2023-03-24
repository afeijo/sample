<?php

namespace Drupal\text_resize\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays the text resize settings form.
 */
class TextResizeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'text_resize.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'text_resize_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('text_resize.settings');
    $form['text_resize_scope'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text Resize Scope'),
      '#default_value' => $config->get('text_resize_scope'),
      '#description' => $this->t('Which portion of the body would you like to be resized by the Text Resize block? You may enter either the CSS class attribute, the CSS id attribute, or an HTML tag.<br />For example, if you want all text within &lt;div id="my-container"&gt; to be resized, enter the ID <strong>my-container</strong>.<br />If, on the other hand, you would like all text within the BODY tag to be resized, enter <strong>body</strong>.'),
      '#required' => TRUE,
    ];
    $form['text_resize_minimum'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default/Minimum Text Size'),
      '#maxlength' => 2,
      '#default_value' => $config->get('text_resize_minimum'),
      '#description' => $this->t('What is the smallest font size (in pixels) that your text can be resized to by users?'),
      '#required' => TRUE,
    ];
    $form['text_resize_maximum'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum Text Size'),
      '#maxlength' => 2,
      '#default_value' => $config->get('text_resize_maximum'),
      '#description' => $this->t('What is the largest font size (in pixels) that your text can be resized to by users?'),
      '#required' => TRUE,
    ];
    $form['text_resize_reset_button'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Reset Button'),
      '#default_value' => $config->get('text_resize_reset_button'),
      '#description' => $this->t('Do you want to add an extra button to the block to allow the font size to be reset to the default/minimum size set above?'),
    ];
    $form['text_resize_line_height_allow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow Line-Height Adjustment'),
      '#default_value' => $config->get('text_resize_line_height_allow'),
      '#description' => $this->t('Do you want to allow Text Resize to change the spacing between the lines of text?'),
    ];
    $form['text_resize_line_height_min'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum Line-Height'),
      '#maxlength' => 2,
      '#default_value' => $config->get('text_resize_line_height_min'),
      '#description' => $this->t('What is the smallest line-height (in pixels) that your text can be resized to by users?'),
    ];
    $form['text_resize_line_height_max'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum Line-Height'),
      '#maxlength' => 2,
      '#default_value' => $config->get('text_resize_line_height_max'),
      '#description' => $this->t('What is the largest line-height (in pixels) that your text can be resized to by users?'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('text_resize.settings')
      ->set('text_resize_scope', $form_state->getValue('text_resize_scope'))
      ->set('text_resize_minimum', $form_state->getValue('text_resize_minimum'))
      ->set('text_resize_maximum', $form_state->getValue('text_resize_maximum'))
      ->set('text_resize_reset_button', $form_state->getValue('text_resize_reset_button'))
      ->set('text_resize_line_height_allow', $form_state->getValue('text_resize_line_height_allow'))
      ->set('text_resize_line_height_min', $form_state->getValue('text_resize_line_height_min'))
      ->set('text_resize_line_height_max', $form_state->getValue('text_resize_line_height_max'))
      ->save();
  }

}
