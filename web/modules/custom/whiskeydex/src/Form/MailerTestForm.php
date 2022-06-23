<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\whiskeydex\Mailer;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class MailerTestForm extends FormBase {

  protected Mailer $mailer;

  public static function create(ContainerInterface $container) {
    $instance = new self();
    $instance->mailer = $container->get('whiskeydex.mailer');
    return $instance;
  }

  public function getFormId(): string {
    return 'whiskeydex_mailer_test_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email_key'] = [
      '#type' => 'select',
      '#options' => [
        'dummy' => 'Dummy',
        'status_canceled' => 'user status_canceled',
        'register_no_approval_required' => 'user register_no_approval_required',
        'register_admin_created' => 'user register_admin_created',
        'password_reset' => 'user password_reset',
        'status_activated' => 'user status_activated',
        'status_blocked' => 'user status_blocked',
      ],
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => 'Send test email',
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email_key = $form_state->getValue('email_key');
    if ($email_key === 'dummy') {
      $this->mailer->sendMail('who@example.com', 'This is a subject', [
        '#markup' => 'What.',
      ]);
    }
    else {
      _user_mail_notify($email_key, User::load($this->currentUser()->id()));
    }
    $this->messenger()->addStatus('Sent email');
  }

}
