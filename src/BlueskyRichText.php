<?php
require_once __DIR__ . '/BlueskyUtils.php';
require_once __DIR__ . '/BlueskyClient.php';

/**
 * Represent a rich text that possibly includes links and tags.
 *
 * Inspired by https://docs.bsky.app/docs/advanced-guides/post-richtext
 */
class BlueskyRichText {
  private string $text;
  private array $urls = [];

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

        array_push($this->urls, $link);
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

  public function generateEmbed (BlueskyClient $client): array | null {
    if (count($this->urls) === 0) {
      return null;
    }

    $url = $this->urls[0];

    $embed = [
      '$type' => 'app.bsky.embed.external',
      'external' => [
        'uri' => $url
      ]
    ];

    $ctx = stream_context_create(array('http'=> [
        'timeout' => 15
      ]
    ));

    // $linkHtml = $http->get($url);
    $linkHtml = file_get_contents($url, false, $ctx);

    if ($linkHtml === null) {
      return null;
    }

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($linkHtml);
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);

    // Get title
    $entries = $xpath->evaluate("//meta[@property='og:title']/@content", $doc);
    if ($entries instanceof DOMNodeList && $entries->count() > 0) {
      $embed['external']['title'] = trim($entries->item(0)->value);
    }

    // Get description
    $entries = $xpath->evaluate("//meta[@property='og:description']/@content", $doc);
    if ($entries instanceof DOMNodeList && $entries->count() > 0) {
      $embed['external']['description'] = trim($entries->item(0)->value);
    }

    // Get image
    try {
      $entries = $xpath->evaluate("//meta[@property='og:image']/@content", $doc);
      if ($entries instanceof DOMNodeList && $entries->count() > 0) {
        $imageUrl = $entries->item(0)->value;

        $entries = $xpath->evaluate("//meta[@property='og:image:type']/@content", $doc);
        if ($entries instanceof DOMNodeList && $entries->count() > 0) {
          $imageMimeType = $entries->item(0)->value;
        }

        $imageData = file_get_contents($imageUrl, false, $ctx);
        $imageMimeType = $imageMimeType ?? mime_content_type($imageData);

        if ($imageData !== false) {
          $uploadResponse = $client->uploadBlob($imageData, $imageMimeType);

          $embed['external']['thumb'] = $uploadResponse['blob'];
        }
      }
    } catch (Exception $e) {
      // Image failed to be processed, ignoring.
      $errorMessage = '[shaarli2bluesky] Could not download or process Open Graph image: [' . $e->getMessage() . '].';
      error_log($errorMessage);
    }

    return $embed;
  }

  public function getText (): string {
    return $this->text;
  }
}
