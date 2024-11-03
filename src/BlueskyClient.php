<?php
require_once __DIR__ . '/BlueskyHttpRequest.php';
require_once __DIR__ . '/BlueskyRichText.php';

class BlueskyClient {

  private $domain = 'bsky.social';

  /**
   * The HTTP instance.
   */
  private $http;

  /**
   * Array of strings where keys and values are separated by colon (:).
   */
  private $headers = [
    'Content-Type: application/json'
  ];

  private $username;
  private $password;

  public function __construct ($username, $password) {
    $this->http = new BlueskyHttpRequest($this->domain);
    $this->username = $username;
    $this->password = $password;
  }

  /**
   * Creates a session and returns the access token.
   */
  private function createSession () {
    $session = $this->http->post(
      $this->http->apiURL . '/com.atproto.server.createSession',
      $this->headers,
      json_encode([
        'identifier' => $this->username,
        'password' => $this->password
      ])
    );

    if (array_key_exists('error', $session)) {
      throw new Exception('Session creation failed with error [' . $session['error'] . '] and message [' . $session['message'] . '].');
    }

    return $session['accessJwt'];
  }

  public function postMessage (BlueskyRichText $message): void {
    $accessToken = $this->createSession();

    if (is_null($accessToken)) {
      throw new Exception('Access token was null.');
    }

    $requestBody = [
      'repo' => $this->username,
      'collection' => 'app.bsky.feed.post',
      'record' => [
        'text' => $message->getText(),
        'createdAt' => date(DATE_ATOM),
        'facets' => $message->generateFacets()
      ]
    ];

    $postResponse = $this->http->post(
      $this->http->apiURL . '/com.atproto.repo.createRecord',
      array_merge(
        $this->headers,
        [ 'Authorization: Bearer ' . $accessToken ]
      ),
      json_encode($requestBody)
    );

    if (array_key_exists('error', $postResponse)) {
      throw new Exception('Error from Bluesky: [' . $postResponse['error'] . '] [' . $postResponse['message'] . '].');
    }
  }
}
