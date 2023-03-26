<?php

namespace Drupal\Currencies;

/**
 * Defines an interface for a currency conversion service.
 */
interface CurrencyConverterInterface {

  /**
   * Converts an amount from one currency to another.
   *
   * @param float $amount
   *   The amount to convert.
   * @param string $from_currency_code
   *   The code for the currency to convert from (e.g. "USD").
   * @param string $to_currency_code
   *   The code for the currency to convert to (e.g. "EUR").
   *
   * @return float
   *   The converted amount.
   */
  public function convertCurrency($amount, $from_currency_code, $to_currency_code);

}
