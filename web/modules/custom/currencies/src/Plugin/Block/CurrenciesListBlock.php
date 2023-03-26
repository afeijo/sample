<?php

namespace Drupal\currencies\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PDO;

/**
 * Provides a 'Currency List' Block.
 *
 * @Block(
 *   id = "currencies_list_block",
 *   admin_label = @Translation("Currencies List Block"),
 * )
 */
class CurrenciesListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * Constructs a new CurrenciesListBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $db
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $db) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->db = $db;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $options = $config['currencies'] ?? [];

    // get the currencies from the db
    $query = $this->db->select('currencies', 'c')
      ->fields('c', ['symbol', 'name', 'rate'])
      ->condition('c.symbol', $options, 'IN')
      ->orderby('rate');
    $currencies = $query->execute()->fetchAllAssoc('symbol', PDO::FETCH_ASSOC);

    foreach($currencies as $symbol => &$currency) {
      $currency['symbol'] = ['data' => $currency['symbol'], 'title' => $currency['name']];
      $currency['rate'] = number_format($currency['rate'], 2);
      unset($currency['name']);
    }

    // build a table with the currencies
    $output = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Currency'),
        $this->t('Rate'),
      ],
      '#rows' => $currencies,
    ];

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    // load all currencies from our db table
    $options = $this->db->select('currencies', 'c')
      ->fields('c', ['symbol', 'name'])
      ->execute()
      ->fetchAllKeyed();
    // Reposition USD and EUR to the top of the array
    $options = [
      'USD' => $options['USD'],
      'EUR' => $options['EUR'],
      ] + $options;

    $form['currencies'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Currencies'),
      '#description' => $this->t('Select which currencies to display.'),
      '#options' => $options,
      '#default_value' => $config['currencies'] ?: ['USD', 'BRL', 'EUR', 'GBP', 'JPY'],
      '#prefix' => $this->t('<style>#edit-settings-currencies{height: 15em; overflow-y: scroll;}#edit-settings-currencies .form-item{float:left;width:15em;}</style>'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->setConfigurationValue('currencies', $values['currencies']);
  }

}
