<?php
require_once __DIR__ . '/BlueskyRichText.php';
require_once __DIR__ . '/BlueskyUtils.php';

/**
 * Represent a Shaarli message to be posted on Bluesky.
 */
class BlueskyMessage {
  const MESSAGE_ALLOWED_PLACEHOLDERS = [
    'url',
    'permalink',
    'title',
    'tags',
    'description'
  ];

  private string $format;
  private int $maxGraphemeLength;
  private array $link;
  private bool $replaceUrlByPermalinkWhenTruncating = false;

  public function __construct (array $link, string $format, string $tagDelimiter, int $maxGraphemeLength, bool $replaceUrlByPermalinkWhenTruncating = false) {
    $link['tags'] = BlueskyUtils::tagify($link['tags'], $tagDelimiter);

    $this->link = $link;
    $this->format = $format;
    $this->maxGraphemeLength = $maxGraphemeLength;
    $this->replaceUrlByPermalinkWhenTruncating = $replaceUrlByPermalinkWhenTruncating;
  }

  public function generateText (): BlueskyRichText {
    return new BlueskyRichText([ 'text' => $this->getTruncatedFormattedText() ]);
  }

  public function getFormattedText (array $placeholdersToShorten = [], bool $replaceUrlByPermalink = false): string {
    $output = $this->format;
    foreach (self::MESSAGE_ALLOWED_PLACEHOLDERS as $placeholder) {
      $placeholderValue = $replaceUrlByPermalink && $placeholder === 'url' ? $this->link['permalink'] : $this->link[$placeholder];

      if (!array_key_exists($placeholder, $placeholdersToShorten)) {
        $output = str_replace('${' . $placeholder . '}', $placeholderValue, $output);
      } else {
        $output = str_replace('${' . $placeholder . '}', BlueskyUtils::shorten($placeholderValue, $placeholdersToShorten[$placeholder]), $output);
      }
    }

    return htmlspecialchars_decode(str_replace('\n', "\n", $output));
  }

  public function getTruncatedFormattedText (): string {
    $formattedText = $this->getFormattedText();

    if (grapheme_strlen($formattedText) <= $this->maxGraphemeLength) {
      return $formattedText;
    }

    $numberOfSegmentsToRemove = 1;
    $lastLoopLength = 0;

    while (grapheme_strlen($formattedText) > $this->maxGraphemeLength) {
      $formattedText = $this->getFormattedText(['description' => $numberOfSegmentsToRemove], $this->replaceUrlByPermalinkWhenTruncating);
      $numberOfSegmentsToRemove++;

      $newLength = grapheme_strlen($formattedText);

      if ($newLength === $lastLoopLength) {
        break;
      }

      $lastLoopLength = $newLength;
    }

    // Worst case, if still too big after shrinking the description, we truncate.
    if (grapheme_strlen($formattedText) > $this->maxGraphemeLength) {
      return grapheme_substr($formattedText, 0, $this->maxGraphemeLength);
    }

    return $formattedText;
  }
}
