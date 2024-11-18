<?php
require __DIR__ . '/../vendor/autoload.php';

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

    $embed = new BlueskyEmbed($this->urls[0]);

    // Fail after 15 seconds waiting for HTML content.
    $ctx = stream_context_create(array('http'=> [
        'timeout' => 15
      ]
    ));

    $linkHtml = file_get_contents($embed->getUri(), false, $ctx);

    if ($linkHtml === null) {
      BlueskyUtils::log('warning', 'Failed to retrieve link content.');
      return null;
    }

    $linkInfo = BlueskyUtils::extractInfoFromHtml($linkHtml);

    // Get title
    $title = $linkInfo['ogTitle'] ?? $linkInfo['title'];

    // Title is mandatory. If missing, embed is skipped entirely
    if ($title === null) {
      BlueskyUtils::log('warning', 'Failed to retrieve link title');
      return null;
    }

    $embed->setTitle($title);

    // Get description
    $description = $linkInfo['ogDescription'];
    if ($description !== null) {
      $embed->setDescription($description);
    }

    // Get image
    try {
      $imageUrl = $linkInfo['ogImage'];
      $imageMimeType = $linkInfo['ogImageType'];

      if ($imageUrl !== null) {
        $imageData = file_get_contents($imageUrl, false, $ctx);

        if ($imageMimeType === null) {
          if (function_exists('mime_content_type')) {
            try {
                $imageMimeType = mime_content_type($imageData);
            } catch (Throwable $e) {
              BlueskyUtils::log('error', '[shaarli2bluesky] Failed to automatically get Open Graph image mime type. Falling back to [image/png].', false);
            }
          }

          $imageMimeType = $imageMimeType ?? 'image/png';
        }

        if ($imageData !== false) {
          $uploadResponse = $client->uploadBlob($imageData, $imageMimeType);

          $embed->setThumb($uploadResponse['blob']);
        }
      }
    } catch (Throwable $e) {
      // Image failed to be processed, ignoring.
      BlueskyUtils::log('warning', 'Could not download or process Open Graph image: [' . $e->getMessage() . '].');
    }

    return $embed->toArray();
  }

  public function getText (): string {
    return $this->text;
  }
}
