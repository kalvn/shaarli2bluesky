<?php

/**
 * shaarli2bluesky
 *
 * Automatically publishes your new Shaarli links to Bluesky.
 * Get Shaarli at https://github.com/shaarli/shaarli
 *
 * See README.md for instructions.
 *
 * @author kalvn <https://mastodon.xyz/@kalvn>
 */

use Shaarli\Config\ConfigManager;
use Shaarli\Plugin\PluginManager;
use Shaarli\Render\TemplatePage;

require_once 'src/BlueskyClient.php';
require_once 'src/BlueskyRichText.php';
require_once 'src/BlueskyMessage.php';
require_once 'src/BlueskyUtils.php';

/**
 * The default message format if none is specified.
 */
const MESSAGE_DEFAULT_FORMAT = '#Shaarli : ${title} ${url} ${tags}';

const POST_PARAM_MESSAGE = 'shaarli2bluesky-message';
const POST_PARAM_MESSAGE_FORMAT = 'shaarli2bluesky-format';

const MESSAGE_MAX_LENGTH = 300;

const DIRECTORY_PATH = __DIR__;

/**
 * Init function: check settings, and set default format.
 *
 * @param ConfigManager $conf instance.
 *
 * @return array|void Error if config is not valid.
 */
function shaarli2bluesky_init ($conf) {
    $format = $conf->get('plugins.BLUESKY_MESSAGE_FORMAT');
    if (empty($format)) {
        $conf->set('plugins.BLUESKY_MESSAGE_FORMAT', MESSAGE_DEFAULT_FORMAT);
    }

    if (!BlueskyUtils::isConfigValid($conf)) {
        return array('Please set up your Bluesky parameters in plugin administration page.');
    }
}

/**
 * Inject CSS when editing a link.
 *
 * @param array $data New link values.
 *
 * @return array $data with the CSS file.
 */
function hook_shaarli2bluesky_render_includes ($data) {
    if (in_array($data['_PAGE_'], [TemplatePage::EDIT_LINK, TemplatePage::EDIT_LINK_BATCH])) {
        $data['css_files'][] = PluginManager::$PLUGINS_PATH . '/shaarli2bluesky/shaarli2bluesky.css';
    }

    return $data;
}

/**
 * Inject CSS when editing a link.
 *
 * @param array $data New link values.
 * @param ConfigManager $conf instance.
 *
 * @return array $data with the JS file.
 */
function hook_shaarli2bluesky_render_footer ($data, $conf) {
    if (in_array($data['_PAGE_'], [TemplatePage::EDIT_LINK, TemplatePage::EDIT_LINK_BATCH])) {
        $data['js_files'][] = PluginManager::$PLUGINS_PATH . '/shaarli2bluesky/shaarli2bluesky.js';
    }

    return $data;
}

/**
 * Hook save link: will automatically publish a message when a new public link is created.
 *
 * @param array $data New link values.
 * @param ConfigManager $conf instance.
 *
 * @return array $data not altered.
 */
function hook_shaarli2bluesky_save_link ($data, $conf) {
    // No message without config, for private links, or on edit.
    if (!BlueskyUtils::isConfigValid($conf)
        || $data['private']
        || !isset($_POST[POST_PARAM_MESSAGE])
    ) {
        return $data;
    }

    // We make sure not to alter data
    $link = array_merge(array(), $data);
    $tagsSeparator = $conf->get('general.tags_separator', ' ');
    $blueskyUsername = $conf->get('plugins.BLUESKY_USERNAME');
    $blueskyPassword = $conf->get('plugins.BLUESKY_PASSWORD');
    $blueskyMessageFormat = isset($_POST[POST_PARAM_MESSAGE_FORMAT]) ? $_POST[POST_PARAM_MESSAGE_FORMAT] : $conf->get('plugins.BLUESKY_MESSAGE_FORMAT', MESSAGE_DEFAULT_FORMAT);
    $blueskyReplaceUrlByPermalinkWhenTruncating = $conf->get('plugins.BLUESKY_REPLACE_URL_BY_PERMALINK_WHEN_TRUNCATING', 'false') === 'true';

    $data['permalink'] = index_url($_SERVER) . 'shaare/' . $data['shorturl'];

    // If the link is a note, we use the permalink as the url.
    if(BlueskyUtils::isLinkNote($data)){
        $data['url'] = $data['permalink'];
    }

    $message = new BlueskyMessage($data, $blueskyMessageFormat, $tagsSeparator, MESSAGE_MAX_LENGTH, $blueskyReplaceUrlByPermalinkWhenTruncating);

    $client = new BlueskyClient($blueskyUsername, $blueskyPassword);

    try {
      $client->postMessage($message->generateText());
    } catch (Throwable $e) {
      error_log('Bluesky API error: '. $e->getMessage());

      if (session_status() == PHP_SESSION_ACTIVE) {
        $_SESSION['errors'][] = 'Something went wrong when publishing the link on Bluesky. ' . $e->getMessage();
      }
    }

    return $link;
}

/**
 * Hook render_editlink: add a checkbox to publish the new link version or not.
 *
 * @param array         $data New link values.
 * @param ConfigManager $conf instance.
 *
 * @return array $data with `edit_link_plugin` placeholder filled.
 */
function hook_shaarli2bluesky_render_editlink ($data, $conf) {
    if (!BlueskyUtils::isConfigValid($conf)) {
        return $data;
    }

    $private = $conf->get('privacy.default_private_links', false);
    $checked = $data['link_is_new'] && !$private;

    $html = file_get_contents(DIRECTORY_PATH . '/edit_link.html');

    $html = str_replace([
      '##checked##',
      '##shaarli2bluesky-format##',
      '##id##',
      '##max-length##',
      '##tags-separator##',
      '##is-note##',
    ], [
      $checked ? 'checked="checked"' : '',
      $conf->get('plugins.BLUESKY_MESSAGE_FORMAT', MESSAGE_DEFAULT_FORMAT),
      uniqid(),
      MESSAGE_MAX_LENGTH,
      $conf->get('general.tags_separator', ' '),
      BlueskyUtils::isLinkNote($data['link']) ? 'true' : 'false',
    ], $html);

    $data['edit_link_plugin'][] = $html;

    return $data;
}
