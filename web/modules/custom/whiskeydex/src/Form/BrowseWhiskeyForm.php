<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

final class BrowseWhiskeyForm extends FormBase {

  public function getFormId() {
    return 'whiskeydex_browse_whiskey_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#action'] = Url::fromRoute('whiskeydex.browse_whiskeys')->toString();
    $form['#method'] = 'get';
    $form['keys'] = [
      '#type' => 'search',
      '#title' => 'Search',
      '#title_display' => 'invisible',
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
    $form['#process'][] = [self::class, 'removeHiddenInputs'];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  public static function removeHiddenInputs(array &$form) {
    $form['form_build_id']['#access'] = FALSE;
    $form['form_token']['#access'] = FALSE;
    $form['form_id']['#access'] = FALSE;
    return $form;
  }

}
