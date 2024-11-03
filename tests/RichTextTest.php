<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class RichTextTest extends TestCase {
  public function __construct () {
    parent::__construct();
  }

  // Links
  public function testTextWithLink (): void {
    $rt = new BlueskyRichText([ 'text' => 'My website: https://kalvn.net?' ]);

    $facets = $rt->generateFacets();

    $this->assertEquals([
      [
        'index' => [
          'byteStart' => 12,
          'byteEnd' => 29
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#link',
            'uri' => 'https://kalvn.net'
          ]
        ]
      ]
    ], $facets);
  }

  // Tags
  public function testTextWithTagInTheMiddleAndComplexEmoji (): void {
    $rt = new BlueskyRichText([ 'text' => 'Super 👨‍👩‍👧‍👦 #emoji ?' ]);

    $facets = $rt->generateFacets();

    $this->assertEquals([
      [
        'index' => [
          'byteStart' => 32,
          'byteEnd' => 38
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'emoji'
          ]
        ]
      ]
    ], $facets);
  }

  public function testTextStartsWithTag (): void {
    $rt = new BlueskyRichText([ 'text' => '#how are you?' ]);

    $facets = $rt->generateFacets();

    $this->assertEquals([
      [
        'index' => [
          'byteStart' => 0,
          'byteEnd' => 4
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'how'
          ]
        ]
      ]
    ], $facets);
  }

  public function testTextWithMultipleTags (): void {
    $rt = new BlueskyRichText([ 'text' => '#this is a good #day to code in #JavaScript !' ]);

    $facets = $rt->generateFacets();

    $this->assertEquals([
      [
        'index' => [
          'byteStart' => 0,
          'byteEnd' => 5
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'this'
          ]
        ]
      ],
      [
        'index' => [
          'byteStart' => 16,
          'byteEnd' => 20
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'day'
          ]
        ]
      ],
      [
        'index' => [
          'byteStart' => 32,
          'byteEnd' => 43
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'JavaScript'
          ]
        ]
      ]
    ], $facets);
  }

  public function testTextWithEdgeCaseTags (): void {
    $rt = new BlueskyRichText([ 'text' => 'Ok so #this_ have #some-funny stuff #in_it_! and a last one #()(3436508-(àéèç🙈)àèçéà\'é"à)àç)' ]);

    $facets = $rt->generateFacets();

    $this->assertEquals([
      [
        'index' => [
          'byteStart' => 6,
          'byteEnd' => 11
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'this'
          ]
        ]
      ],
      [
        'index' => [
          'byteStart' => 18,
          'byteEnd' => 29
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'some-funny'
          ]
        ]
      ],
      [
        'index' => [
          'byteStart' => 36,
          'byteEnd' => 42
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => 'in_it'
          ]
        ]
      ],
      [
        'index' => [
          'byteStart' => 60,
          'byteEnd' => 107
        ],
        'features' => [
          [
            '$type' => 'app.bsky.richtext.facet#tag',
            'tag' => '()(3436508-(àéèç🙈)àèçéà\'é"à)àç'
          ]
        ]
      ]
    ], $facets);
  }
}
