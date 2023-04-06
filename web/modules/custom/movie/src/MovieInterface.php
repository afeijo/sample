<?php

namespace Drupal\movie;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a movie entity type.
 */
interface MovieInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
