<?php

class BlueskyEmbed {
  private string $type = 'app.bsky.embed.external';
  private string $uri;
  private ?string $title = null;
  private ?string $description = null;

  /**
   * {
   *   "type": "blob",
   *   "ref": {
   *     "$link": string
   *   },
   *   "mimeType": string
   *   "size": int
   * }
   */
  private ?array $thumb = null;

  public function __construct(string $uri) {
    $this->uri = $uri;
  }

  public function toArray (): ?array {
    if ($this->uri === null) {
      return null;
    }

    $result = [
      '$type' => $this->type,
      'external' => [
        'uri' => $this->uri,
        'title' => $this->title ?? $this->uri,
        'description' => $this->description ?? ''
      ]
    ];

    if ($this->thumb) {
      $result['external']['thumb'] = $this->thumb;
    }

    return $result;
  }

  public function getUri (): string {
    return $this->uri;
  }
  public function setUri (string $uri) {
    $this->uri = $uri;
    return $this;
  }

  public function getTitle (): ?string {
    return $this->title;
  }
  public function setTitle (?string $title) {
    $this->title = $title;
    return $this;
  }

  public function getDescription (): ?string {
    return $this->description;
  }
  public function setDescription (?string $description) {
    $this->description = $description;
    return $this;
  }

  public function getThumb (): ?array {
    return $this->thumb;
  }
  public function setThumb(?array $thumb) {
    $this->thumb = $thumb;
    return $this;
  }
}
