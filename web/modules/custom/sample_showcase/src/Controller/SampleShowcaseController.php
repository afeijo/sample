<?php

namespace Drupal\sample_showcase\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Builds sample-site showcase pages.
 */
final class SampleShowcaseController extends ControllerBase {

  /**
   * Builds the front showcase page.
   */
  public function home(): array {
    $cards = [
      [
        'title' => 'LiveUpdate Remote',
        'meta' => 'Recommended replacement for the currency sample',
        'body' => 'Turns a public JSON API into a cached, live-refreshing Drupal block. The sample uses Frankfurter currency rates without exposing visitors to the provider API.',
        'route' => 'sample_showcase.liveupdate_remote',
      ],
      [
        'title' => 'Timezone Picker',
        'meta' => 'Form UX',
        'body' => 'Replaces timezone selects with a responsive world-map picker for regional settings and user profile timezone fields.',
        'route' => 'sample_showcase.timezone_picker',
      ],
      [
        'title' => 'Page Attributes',
        'meta' => 'Editorial control',
        'body' => 'Adds per-node body IDs, body classes, and article classes so editors can target landing pages without theme code changes.',
        'route' => 'sample_showcase.page_attributes',
      ],
      [
        'title' => 'Inline Style Aggregation',
        'meta' => 'Performance hygiene',
        'body' => 'Collects scattered inline style tags into one head style element, reducing DOM noise while preserving media attributes.',
        'route' => 'sample_showcase.inline_style_aggregation',
      ],
      [
        'title' => 'Currencies',
        'meta' => 'Legacy custom module',
        'body' => 'The original custom currency module remains as a code sample, but LiveUpdate Remote is the cleaner reusable approach for remote JSON data.',
        'route' => 'sample_showcase.currencies',
      ],
      [
        'title' => 'Movie Entity',
        'meta' => 'Custom entity sample',
        'body' => 'A compact custom entity module with fields, routes, permissions, REST normalization, and Views configuration.',
        'route' => 'entity.movie.collection',
      ],
    ];

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['sample-showcase']],
      '#attached' => ['library' => ['sample_showcase/showcase']],
      'intro' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['sample-showcase__intro']],
        'title' => ['#markup' => '<h1>Feijó Sample Site</h1>'],
        'copy' => ['#markup' => '<p>A Drupal 11 playground for small modules, upgrade readiness, and practical site-builder patterns.</p>'],
      ],
      'cards' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['sample-showcase__grid']],
        'items' => array_map([$this, 'buildCard'], $cards),
      ],
    ];
  }

  /**
   * Builds the LiveUpdate Remote page.
   */
  public function liveUpdateRemote(): array {
    return $this->panel('LiveUpdate Remote currencies', [
      'This page demonstrates the preferred replacement for the custom currency polling logic: a reusable remote JSON source rendered as a live-updating Drupal block.',
      'The source uses https://api.frankfurter.app/latest?from=USD&to=BRL,EUR,GBP with a 5 minute server-side cache.',
      'Visitors refresh a Drupal fragment. The browser never calls the currency provider directly.',
    ], [], 'sample-showcase__remote');
  }

  /**
   * Builds the Page Attributes page.
   */
  public function pageAttributes(): array {
    return $this->panel('Page Attributes', [
      'Page Attributes is best shown on editable node pages. It adds a Page attributes details section to node forms for body ID, body classes, and article classes.',
      'The values are revision-aware and are applied only when the canonical node page is rendered.',
      'This is useful for campaign pages, one-off CSS hooks, and migrations where legacy page classes need to survive.',
    ]);
  }

  /**
   * Builds the Timezone Picker page.
   */
  public function timezonePicker(): array {
    return $this->panel('Timezone Picker', [
      'Timezone Picker improves the regional settings and user timezone fields with a clickable map instead of a long select list.',
      'The admin configuration lives at /admin/config/regional/timezone-picker.',
      'It is a focused UX module, so the sample page points to where editors and administrators naturally encounter it.',
    ]);
  }

  /**
   * Builds the Inline Style Aggregation page.
   */
  public function inlineStyleAggregation(): array {
    return $this->panel('Inline Style Aggregation', [
      'Inline Style Aggregation runs late on HTML responses and combines inline style tags into a single style element.',
      'It can include selected head styles, preserve media attributes, and optionally minify CSS.',
      'The sample enables it globally so rendered pages demonstrate the optimized output directly.',
    ]);
  }

  /**
   * Builds the legacy currencies page.
   */
  public function currencies(): array {
    return $this->panel('Currencies', [
      'Currencies remains in the repository as a custom module sample: schema, cron, service, configuration form, and block plugin.',
      'For a public sample site, LiveUpdate Remote is a better showcase for remote JSON data because it is generic, cached, configurable, and does not hard-code one provider.',
      'The custom module is still useful as code-reading material, but the home promotes LiveUpdate Remote as the recommended path.',
    ]);
  }

  /**
   * Builds a reusable card.
   */
  private function buildCard(array $card): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['sample-showcase__card']],
      'meta' => ['#markup' => '<div class="sample-showcase__meta">' . $card['meta'] . '</div>'],
      'title' => ['#markup' => '<h2>' . $card['title'] . '</h2>'],
      'body' => ['#markup' => '<p>' . $card['body'] . '</p>'],
      'actions' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['sample-showcase__actions']],
        'link' => Link::fromTextAndUrl($this->t('Open'), Url::fromRoute($card['route']))->toRenderable() + [
          '#attributes' => ['class' => ['sample-showcase__button']],
        ],
      ],
    ];
  }

  /**
   * Builds a text panel page.
   */
  private function panel(string $title, array $items, array $extra = [], string $class = ''): array {
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => array_filter(['sample-showcase', $class])],
      '#attached' => ['library' => ['sample_showcase/showcase']],
      'panel' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['sample-showcase__panel']],
        'title' => ['#markup' => '<h1>' . $title . '</h1>'],
        'list' => ['#markup' => '<ul class="sample-showcase__list"><li>' . implode('</li><li>', $items) . '</li></ul>'],
      ],
    ];

    foreach ($extra as $key => $value) {
      $build[$key] = $value;
    }
    return $build;
  }

}
