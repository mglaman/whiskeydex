<?php declare(strict_types=1);

namespace Drupal\whiskeydex;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;

final class Mailer {

  public function __construct(
    private readonly MailManagerInterface $mailManager,
    private readonly LanguageManagerInterface $languageManager,
    private readonly ConfigFactoryInterface $configFactory
  ) {
  }

  /**
   * @phpstan-param array<int, string> $body
   * @phpstan-param array{id?: string, cc?: string, bcc?: string, langcode?: string, reply-to?: string|null} $params
   */
  public function sendMail(string $to, string $subject, array $body, array $params = []): bool {
    $default_params = [
      'headers' => [
        'Content-Type' => 'text/html; charset=UTF-8;',
        'Content-Transfer-Encoding' => '8Bit',
      ],
      'id' => 'mail',
      'from' => $this->configFactory->get('system.site')->get('mail_notification'),
      'reply-to' => NULL,
      'subject' => $subject,
      'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
      'body' => $body,
    ];
    if (!empty($params['cc'])) {
      $default_params['headers']['Cc'] = $params['cc'];
    }
    if (!empty($params['bcc'])) {
      $default_params['headers']['Bcc'] = $params['bcc'];
    }
    $params = array_replace($default_params, $params);
    // @phpstan-ignore-next-line
    $message = $this->mailManager->mail('whiskeydex', $params['id'], $to, $params['langcode'], $params, $params['reply-to']);
    $message += ['to' => $to];
    return (bool) $message['result'];
  }

}
