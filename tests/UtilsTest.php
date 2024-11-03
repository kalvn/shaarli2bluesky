<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase {

  public function __construct () {
    parent::__construct();
  }

  public function testIsLinkNote (): void {
    $this->assertTrue(BlueskyUtils::isLinkNote([
      'url' => '/shaare/i6lwMw'
    ]));

    $this->assertFalse(BlueskyUtils::isLinkNote([
      'url' => 'https://github.com/kalvn/shaarli2bluesky'
    ]));
  }

  public function testExtractTags (): void {
    $matches = BlueskyUtils::extractTags('a #a1a_1 bonj', true);
    $this->assertEquals([
      [
        ['#a1a_1', 2]
      ],
      [
        ['a1a_1', 3]
      ]
    ], $matches);
  }

  public function testExtractUrls (): void {
    $matches = BlueskyUtils::extractUrls('My site\'s URL is: https://kalvn.net (I also post links at https://links.kalvn.net/about?foo=bar).', true);

    $this->assertEquals([
      [
        ['https://kalvn.net', 18],
        ['https://links.kalvn.net/about?foo=bar', 58]
      ],
      [
        ['https://kalvn.net', 18],
        ['https://links.kalvn.net/about?foo=bar', 58]
      ]
    ], $matches);
  }
}
