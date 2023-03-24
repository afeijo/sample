<?php

/**
 * @file
 * Test fixture for the 'administer text_resize' permission update path.
 */

use Drupal\Core\Database\Database;
use Drupal\Core\Serialization\Yaml;

$connection = Database::getConnection();

$connection->insert('key_value')
  ->fields([
    'collection',
    'name',
    'value',
  ])
  ->values([
    'collection' => 'system.schema',
    'name' => 'text_resize',
    'value' => serialize(8100),
  ])
  ->execute();

// Update core.extension.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions, ['allowed_classes' => FALSE]);
$extensions['module']['text_resize'] = 0;
$connection->update('config')
  ->fields(['data' => serialize($extensions)])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();

$text_resize_settings = Yaml::decode(file_get_contents(__DIR__ . '/text_resize.settings.yml'));
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'text_resize.settings',
    'data' => serialize($text_resize_settings),
  ])
  ->execute();
