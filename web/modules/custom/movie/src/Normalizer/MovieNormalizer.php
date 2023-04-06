<?php

namespace Drupal\movie\Normalizer;

use Drupal\movie\Entity\Movie;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;

/**
 * Modifies the Movie entity normalizer to change JSON output.
 */
class MovieNormalizer extends ContentEntityNormalizer {

  protected $node;

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, ?string $format = NULL, array $context = []): bool {
    return $data instanceof Movie;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {
    // Get the default normalized output from the parent method.
    $normalized = parent::normalize($entity, $format, $context);

    $unset_list = [
      'uid',
      'uuid',
      'status',
      'description',
      'created',
      'changed',
      'label',
      'field_release_date',
      'field_genre',
    ];

    $normalized['title'] = $normalized['label'][0]['value'];
    $normalized['release_date'] = $normalized['field_release_date'][0]['value'];

    $movie = Movie::load($normalized['id'][0]['value']);
    $normalized['genre'] = $movie->field_genre->entity->label();

    foreach($unset_list as $unset) {
      unset($normalized[$unset]);
    }

    $normalized['id'] = $normalized['id'][0]['value'];

    // Return the modified output.
    return $normalized;
  }

}
