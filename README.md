# shaarli2bluesky

This plugin allows you to automatically publish links you post on [Bluesky](https://bsky.app).

## TODO
- [ ] Take into account the fact that apparently, URLs only count up to 39 characters. Every extra character is free.

## Requirements

- PHP 8.0 minimum
- PHP extensions
  - intl
  - curl
- Shaarli >= v0.8.1 in public mode (which is the default mode)


## Installation
### 1. Create a new app password
In Bluesky, go to [Settings > Application passwords](https://bsky.app/settings/app-passwords) and create a new one.

Choose any name you like (`shaarli2bluesky` is a good choice) and submit. Then, copy the password, you'll need it later.

### 2. Install the plugin
[Download the latest version from releases](https://github.com/kalvn/shaarli2bluesky/releases) and decompress files under `/plugins/shaarli2bluesky` directory of your Shaarli installation.

Below is an example with command line.

Run the following command from within the `/plugins` directory:

```bash
$ wget https://github.com/kalvn/shaarli2bluesky/archive/refs/tags/v0.0.5.tar.gz
$ tar -xvzf v0.0.5.tar.gz
$ mv shaarli2bluesky-0.0.5 shaarli2bluesky
$ rm v0.0.5.tar.gz
```

Make sure these new files are readable by your web server (Apache, Nginx, etc.).

Then, on your Shaarli instance, go to *Plugin administration* page and activate the plugin.

### 3. Configure the plugin
Your parameters from step 1 will be used here. After plugin activation, you'll see 5 parameters.

- **BLUESKY_USERNAME**: Your Bluesky handle, example: *incredible.bsky.social*
- **BLUESKY_PASSWORD**: The app password you generated at step 1
- **BLUESKY_MESSAGE_FORMAT**: The format of your messages. Available placeholders:
    + *${url}*: URL of link shared
    + *${permalink}*: permalink of the share
    + *${title}*: title of the share
    + *${description}*: description of the share
    + *${tags}*: tags of the share, prefixed with #
- **BLUESKY_REPLACE_URL_BY_PERMALINK_WHEN_TRUNCATING**: If set to "true", when a message is too long to be published as-is, the URL is replaced by the permalink so that your Bluesky followers have a chance to read the full message on your Shaarli.


## Test

```bash
composer install
composer test
# or
./vendor/bin/phpunit tests
```

Or with Docker:

```bash
docker run -v $(pwd):/app --rm composer install
docker run -v $(pwd):/app --rm composer test
```
