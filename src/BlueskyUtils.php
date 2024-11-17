<?php
use PHPHtmlParser\Dom;

class BlueskyUtils {

  /**
   * Determines whether the configuration is valid or not.
   *
   * @param  ConfigManager $conf Configuration instance.
   * @return boolean Whether the config is valid or not.
   */
  public static function isConfigValid ($conf) {
    $mandatory = [
      'BLUESKY_USERNAME',
      'BLUESKY_PASSWORD',
    ];
    foreach ($mandatory as $value) {
      $setting = $conf->get('plugins.'. $value);
      if (empty($setting)) {
        return false;
      }
    }
    return true;
  }

  /**
   * Determines if the link is a note.
   * @param  array  $link The link to check.
   * @return boolean      Whether the link is a note or not.
   */
  public static function isLinkNote ($link) {
    return !preg_match('/^http[s]?:/', $link['url']);
  }

  /**
   * Converts a string of concatenated tags into a list of hashtags separated by space.
   *
   * TODO: improve the sanitization of resulting tag string to be more permissive.
   */
  public static function tagify ($tagsStr, $tagDelimiter): string{
    // Regex inspired by https://gist.github.com/janogarcia/3946583
    if (empty($tagsStr)) {
      return '';
    }

    $tags = explode($tagDelimiter, $tagsStr);

    $result = [];

    foreach ($tags as $tag) {
      array_push($result, '#' . preg_replace('/[^0-9_\-\p{L}]/u', '', $tag));
    }

    return implode(' ', $result);
  }

  /**
   * Extracts tags from a given string.
   *
   * Matches anything that ends with a letter or a digit and contains at least 1 letter.
   */
  public static function extractTags (string $text, bool $withOffset = false): array {
    $re = '/#(?=.*[\p{L}]+)([^\s]*[\p{L}\p{N}])+/u';
    $success = preg_match_all($re, $text, $matches, $withOffset ? PREG_OFFSET_CAPTURE : 0);

    if ($success === false || $success === 0) {
      return [];
    }

    return $matches;
  }

  /**
   * Extracts URLs from a given string.
   *
   * Starts with http(s)://. Cannot end with . ! ? )
   */
  public static function extractUrls (string $text, bool $withOffset = false): array {
    $re = '/(https?:\/\/[^\s]*[^.!?)\s])/u';
    $success = preg_match_all($re, $text, $matches, $withOffset ? PREG_OFFSET_CAPTURE : 0);

    if ($success === false || $success === 0) {
      return [];
    }

    return $matches;
  }

  /**
   * Shorten a string by $numberOfSegmentsToRemove segments (=words), and adds ellipsis at the end.
   */
  public static function shorten (string $text, int $numberOfSegmentsToRemove): string {
    $segments = explode(' ', $text);
    array_splice($segments, -$numberOfSegmentsToRemove);
    return implode(' ', $segments) . (count($segments) > 0 ? '…' : '');
  }

  public static function extractInfoFromHtml ($html): array {
    $result = [];
    $dom = new Dom();
    $dom->loadStr($html);

    $title = $dom->find('head title')[0];
    $result['title'] = $title?->text();

    $ogTitleEl = $dom->find('meta[property="og:title"]')[0];
    $result['ogTitle'] = $ogTitleEl?->getAttribute('content');

    $ogDescriptionEl = $dom->find('meta[property="og:description"]')[0];
    $result['ogDescription'] = $ogDescriptionEl?->getAttribute('content');

    $ogImageEl = $dom->find('meta[property="og:image"]')[0];
    $result['ogImage'] = $ogImageEl?->getAttribute('content');

    $ogImageTypeEl = $dom->find('meta[property="og:image:type"]')[0];
    $result['ogImageType'] = $ogImageTypeEl?->getAttribute('content');

    return $result;
  }
}
