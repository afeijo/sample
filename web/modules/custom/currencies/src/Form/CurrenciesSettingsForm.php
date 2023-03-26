<?php

namespace Drupal\currencies\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;

/**
 * Class CurrenciesSettings.
 *
 * @package Drupal\currencies
 */
class CurrenciesSettingsForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  // currencies.currency_converter service
  protected $converter;

  /**
   * Constructs a new CurrenciesSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    $this->converter = \Drupal::service('currencies.currency_converter');
  }

  /**
   * Implements getFormId
   */
  public function getFormId() {
    return 'currencies_settings';
  }

  /**
   * Implements getEditableConfigNames
   * @return array
   */
  protected function getEditableConfigNames() {
    return [
      'currencies.settings',
    ];
  }

  /**
   * Returns the currencies settings.
   *
   * @return array
   *   The currencies settings.
   */
  public function getSettings() {
    $config = $this->configFactory->get('currencies.settings');

    return [
      'currencies' => $config->get('currencies'),
      'default_currency' => $config->get('default_currency'),
      'api_key' => $config->get('api_key'),
    ];
  }

  /**
   * Returns the currencies settings form.
   *
   * @return array
   *   The currencies settings form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $currencies = $this->getCurrencyList();
    // Reposition USD and EUR to the top of the array
    $currencies = [
      'USD' => $currencies['USD'],
      'EUR' => $currencies['EUR'],
      ] + $currencies;

    foreach ($currencies as $sym => $values) {
      $rate = number_format($values['rate'], 2);
      $cur[$sym] = $values['name'] . ' ($ ' . $rate . ')';
    }

    $form['currencies'] = [
      '#type' => 'checkboxes',
      '#options' => (array) $cur,
      '#title' => $this->t('Currencies'),
      '#description' => $this->t('Select the desired currencies.'),
      '#default_value' => $settings['currencies'] ?: ['USD', 'BRL', 'EUR', 'GBP', 'JPY'],
      '#prefix' => $this->t('<style>#edit-currencies{height: 15em; overflow-y: scroll;}#edit-currencies .form-item{float:left;width:15em;}</style>'),
    ];

    $form['default_currency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default currency'),
      '#description' => $this->t('Enter the default currency.'),
      '#default_value' => $settings['default_currency'],
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#description' => $this->t('Enter the Fixer.io API key.'),
      '#default_value' => $settings['api_key'] ?? 'MJwajByKJnTzn7Gy6tfmxHHG5r4LCSez',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('currencies.settings')
      ->set('currencies', array_filter($form_state->getValue('currencies'), null, ARRAY_FILTER_USE_BOTH))
      ->set('default_currency', $form_state->getValue('default_currency'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    // dpm($this->converter->convertCurrency(100, 'USD', 'BRL'));
    parent::submitForm($form, $form_state);
  }

  /**
   * Summary of getCurrencyList
   * @return bool|string
   */
  public function getCurrencyList(): array {
    $settings = $this->getSettings();
    $currencies = $settings['currencies'];

    // query all records from currencies db table
    $query = \Drupal::database()->select('currencies', 'c');
    $query->fields('c', ['symbol', 'name', 'rate', 'updated']);
    $query->condition('updated', time() - 86400, '>');
    $rows = $query->execute()->fetchAll();
    if (count($rows) == 0) {
      currenciesDownload();
      return $this->getCurrencyList();
    }

    $currencies = [];
    foreach ($rows as $row) {
      $currencies[$row->symbol] = [
        'name' => $row->name,
        'rate' => $row->rate,
      ];
    }

    return $currencies;
  }
}
