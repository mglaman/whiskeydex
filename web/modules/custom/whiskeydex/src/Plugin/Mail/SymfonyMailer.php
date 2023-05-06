<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Plugin\Mail;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Random;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Html2Text\Html2Text;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * @Mail(
 *   id = "symfony_mailer",
 *   label = @Translation("Symfony Mailer"),
 *   description = @Translation("Symfony Mailer Plugin.")
 * )
 *
 * Basically a port of Swiftmailer module for Symfony Mailer.
 */
final class SymfonyMailer implements MailInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly RendererInterface $renderer,
    private readonly MimeTypeGuesserInterface $mimeTypeGuesser,
    private readonly ThemeManagerInterface $themeManager,
    private readonly ThemeInitializationInterface $themeInitialization,
    private readonly AssetResolverInterface $assetResolver,
    private readonly EventDispatcherInterface $eventDispatcher
  ) {
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new self(
      $container->get('renderer'),
      $container->get('file.mime_type.guesser'),
      $container->get('theme.manager'),
      $container->get('theme.initialization'),
      $container->get('asset.resolver'),
      $container->get('event_dispatcher')
    );
  }

  // @todo mailsystem is what switched to normal theme for rendering.
  public function format(array $message) {
    $is_html = $this->getContentType($message) === 'text/html';
    // @todo are params handling legacy swiftmailer?
    $generate_plain = $message['params']['generate_plain'] ?? $message['params']['convert'] ?? TRUE;
    if ($generate_plain && empty($message['plain']) && $is_html) {
      $saved_body = $message['body'];
      $this->massageMessageBody($message, FALSE);
      $message['plain'] = $message['body'];
      $message['body'] = $saved_body;
    }
    $this->massageMessageBody($message, $is_html);

    // We replace all 'image:foo' in the body with a unique magic string like
    // 'cid:[randomname]' and keep track of this. It will be replaced by the
    // final "cid" in ::embed().
    $random = new Random();
    $embeddable_images = [];
    $processed_images = [];
    preg_match_all('/"image:([^"]+)"/', $message['body'], $embeddable_images);
    for ($i = 0, $v = count($embeddable_images[0]); $i < $v; $i++) {
      $image_id = $embeddable_images[0][$i];
      if (isset($processed_images[$image_id])) {
        continue;
      }
      $image_path = trim($embeddable_images[1][$i]);
      $image_name = basename($image_path);

      if (str_starts_with($image_path, '/')) {
        $image_path = mb_substr($image_path, 1);
      }

      $image = new \stdClass();
      $image->uri = $image_path;
      $image->filename = $image_name;
      $image->filemime = $this->mimeTypeGuesser->guessMimeType($image_path);
      $image->cid = $random->name(8, TRUE);
      $message['params']['images'][] = (object) $image;
      $message['body'] = preg_replace($image_id, 'cid:' . $image->cid, $message['body']);
      $processed_images[$image_id] = 1;
    }

    return $message;
  }

  public function mail(array $message) {
    try {
      $mailer_type = getenv('MAIL_MAILER');
      if ($mailer_type === 'smtp') {
        $dsn = sprintf(
          'smtp://%s:%s@%s:%s',
          urlencode(getenv('MAIL_USERNAME')),
          urlencode(getenv('MAIL_PASSWORD')),
          getenv('MAIL_HOST'),
          getenv('MAIL_PORT')
        );
      }
      elseif ($mailer_type === 'ses') {
        $dsn = sprintf(
          'ses+smtp://%s:%s@default?region=us-east-1',
          urlencode(getenv('MAIL_USERNAME')),
          urlencode(getenv('MAIL_PASSWORD')),
        );
      }
      else {
        $dsn = 'null://';
      }
      $transport = Transport::fromDsn($dsn);
      $mailer = new Mailer($transport, NULL, $this->eventDispatcher);

      $email = (new Email())
        ->from($message['from'])
        ->to($message['to'])
        ->subject($message['subject'])
        ->text($message['plain'])
        ->html($message['body']);
      if ($message['reply-to']) {
        $email->replyTo($message['reply-to']);
      }
      $mailer->send($email);
      return TRUE;
    }
    catch (\Exception $e) {
      // @todo add logging.
    }
    return FALSE;
  }

  private function massageMessageBody(array &$message, bool $is_html) {
    $text_format = $message['params']['text_format'] ?? filter_fallback_format() ?: NULL;
    $line_endings = PHP_EOL;
    $body = [];

    foreach ($message['body'] as $part) {
      if (!($part instanceof MarkupInterface)) {
        if ($is_html) {
          // Convert to HTML. The default 'plain_text' format escapes markup,
          // converts new lines to <br> and converts URLs to links.
          $body[] = check_markup($part, $text_format);
        }
        else {
          // The body will be plain text. However we need to convert to HTML
          // to render the template then convert back again. Use a fixed
          // conversion because we don't want to convert URLs to links.
          $body[] = preg_replace("|\n|", "<br />\n", HTML::escape($part)) . "<br />\n";
        }
      }
      else {
        $body[] = $part . $line_endings;
      }
    }

    // Merge all lines in the e-mail body and treat the result as safe markup.
    $message['body'] = Markup::create(implode('', $body));

    $current_active_theme = $this->themeManager->getActiveTheme();
    try {
      if ($current_active_theme->getName() !== 'distilled') {
        $this->themeManager->setActiveTheme($this->themeInitialization->initTheme('distilled'));
      }

      $render = [
        '#theme' => $message['params']['theme'] ?? 'whiskeydex_mail',
        '#message' => $message,
        '#is_html' => $is_html,
      ];

      if ($is_html) {
        $render['#attached']['library'] = ['distilled/mailer'];
      }

      $message['body'] = $this->renderer->renderPlain($render);
    }
    finally {
      if ($current_active_theme->getName() !== 'distilled') {
        $this->themeManager->setActiveTheme($current_active_theme);
      }
    }

    if ($is_html) {
      // Process CSS from libraries.
      $assets = AttachedAssets::createFromRenderArray($render);
      $css = '';
      // Request optimization so that the CssOptimizer performs essential
      // processing such as @include.
      foreach ($this->assetResolver->getCssAssets($assets, TRUE) as $css_asset) {
        // @todo in Cypress `data` is empty, debug!
        if (!empty($css_asset['data'])) {
          $css .= file_get_contents($css_asset['data']);
        }
      }

      if ($css) {
        $message['body'] = (new CssToInlineStyles())->convert($message['body'], $css);
      }
      // Ensure cast to string.
      else {
        $message['body'] = (string) $message['body'];
      }
    }
    else {
      // Convert to plain text.
      $message['body'] = (new Html2Text($message['body']))->getText();
    }
  }

  private function getContentType(array $message): string {
    // @todo are params handling legacy swiftmailer?
    $content_type = $message['params']['content_type'] ?? $message['params']['format'] ?? 'text/html';
    if ($content_type) {
      return $content_type;
    }
    if (isset($message['headers']['Content-Type'])) {
      return explode(';', $message['headers']['Content-Type'])[0];
    }
    return 'text/plain';
  }

}
