<?php

namespace Drupal\currencies;

use Drupal\currencies\CurrencyConverterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a currency conversion service.
 */
class CurrencyConverter implements ContainerInjectionInterface, CurrencyConverterInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * Constructs a new CurrencyConverter object.
   */
  public function __construct(Connection $db) {
    $this->db = $db;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function convertCurrency($amount, $from_currency_code, $to_currency_code) {
    // Return currencies rate from our mysql db table.
    $query = $this->db->select('currencies', 'c')
     ->condition('symbol', [$from_currency_code, $to_currency_code], 'in')
      ->fields('c', ['symbol', 'rate'])
      ->execute();
    $rates = $query->fetchAllKeyed();
    return $amount * $rates[$to_currency_code] / $rates[$from_currency_code];
  }

}
