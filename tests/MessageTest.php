<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase {
  public function testMessageOutput (): void {
    $message = new BlueskyMessage([
      'url' => 'https://links.kalvn.net',
      'permalink' => 'http://localhost:8080/shaare/HYDUug',
      'title' => 'kalvn\'s links',
      'tags' => 'tech dev javascript',
      'description' => 'A fancy website'
    ], '${description}\n\n${title}\n${url}\n\n${tags}', ' ', 300);

    $this->assertEquals("A fancy website\n\nkalvn's links\nhttps://links.kalvn.net\n\n#tech #dev #javascript", $message->getFormattedText());
  }

  public function testMessageTruncatedOutput (): void {
    $message = new BlueskyMessage([
      'url' => 'https://links.kalvn.net',
      'permalink' => 'http://localhost:8080/shaare/HYDUug',
      'title' => 'kalvn\'s links',
      'tags' => 'tech dev javascript',
      'description' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim'
    ], '${description}\n\n${title}\n${url}\n\n${tags}', ' ', 300);

    $this->assertEquals("Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec,…\n\nkalvn's links\nhttps://links.kalvn.net\n\n#tech #dev #javascript", $message->getTruncatedFormattedText());
  }

  public function testMessageTruncatedUrlReplacementOutput (): void {
    $message = new BlueskyMessage([
      'url' => 'https://links.kalvn.net',
      'permalink' => 'http://localhost:8080/shaare/HYDUug',
      'title' => 'kalvn\'s links',
      'tags' => 'tech dev javascript',
      'description' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim'
    ], '${description}\n\n${title}\n${url}\n\n${tags}', ' ', 300, true);

    $this->assertEquals("Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec,…\n\nkalvn's links\nhttp://localhost:8080/shaare/HYDUug\n\n#tech #dev #javascript", $message->getTruncatedFormattedText());
  }

  public function testMessageTruncatedHardcoreOutput (): void {
    $message = new BlueskyMessage([
      'url' => 'https://links.kalvn.net',
      'permalink' => 'http://localhost:8080/shaare/HYDUug',
      'title' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim',
      'tags' => 'tech dev javascript',
      'description' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim'
    ], '${description}\n\n${title}\n${url}\n\n${tags}', ' ', 300);

    $this->assertEquals("\n\nLorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Done", $message->getTruncatedFormattedText());
  }
}
