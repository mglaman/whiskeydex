<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

final class BrowseWhiskeyForm extends FormBase {

  public function getFormId(): string {
    return 'whiskeydex_browse_whiskey_form';
  }

  /**
   * @phpstan-param array<string, mixed> $form
   * @phpstan-return array<string, mixed>
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#action'] = Url::fromRoute('whiskeydex.browse_whiskeys')->toString();
    $form['#method'] = 'get';
    $form['keys'] = [
      '#type' => 'search',
      '#title' => 'Search',
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Search for a whiskey'),
      '#size' => 15,
      '#default_value' => $this->getRequest()->query->get('keys', ''),
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      // Prevent op from showing up in the query string.
      '#name' => '',
    ];
    // @phpstan-ignore-next-line
    $form['#process'][] = [self::class, 'removeHiddenInputs'];
    return $form;
  }

  /**
   * @phpstan-param array<string, mixed> $form
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * @phpstan-param array<string, mixed> $form
   * @phpstan-return array<string, mixed>
   */
  public static function removeHiddenInputs(array &$form): array {
    // @phpstan-ignore-next-line
    $form['form_build_id']['#access'] = FALSE;
    // @phpstan-ignore-next-line
    $form['form_token']['#access'] = FALSE;
    // @phpstan-ignore-next-line
    $form['form_id']['#access'] = FALSE;
    return $form;
  }

}
