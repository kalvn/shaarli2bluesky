<?php
require_once __DIR__ . '/BlueskyUtils.php';

/**
 * Represent a rich text that possibly includes links and tags.
 *
 * Inspired by https://docs.bsky.app/docs/advanced-guides/post-richtext
 */
class BlueskyRichText {
  private string $text;

  public function __construct (array $options) {
    $this->text = $options['text'];
  }

  public function generateFacets (): array {
    $facets = [];

    // Links - starts with http(s). Cannot end with . ! ? )
    $linksMatches = BlueskyUtils::extractUrls($this->text, true);

    if (count($linksMatches) >= 2) {
      foreach ($linksMatches[0] as $match) {
        $link = $match[0];
        $byteStart = $match[1];

        array_push($facets, [
          'index' => [
            'byteStart' => $byteStart,
            'byteEnd' => $byteStart + strlen($link)
          ],
          'features' => [
            [
              '$type' => 'app.bsky.richtext.facet#link',
              'uri' => $link
            ]
          ]
        ]);
      }
    }

    // Tags - must just end with letter or digit and contain at least 1 letter
    $tagsMatches = BlueskyUtils::extractTags($this->text, true);

    if (count($tagsMatches) >= 2) {
      foreach ($tagsMatches[0] as $match) {
        $tag = $match[0];
        $byteStart = $match[1];

        array_push($facets, [
          'index' => [
            'byteStart' => $byteStart,
            'byteEnd' => $byteStart + strlen($tag)
          ],
          'features' => [
            [
              '$type' => 'app.bsky.richtext.facet#tag',
              'tag' => str_replace('#', '', $tag)
            ]
          ]
        ]);
      }
    }

    return $facets;
  }

  public function getText (): string {
    return $this->text;
  }
}
