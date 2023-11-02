<?php

declare(strict_types=1);

namespace Drupal\whiskeydex\Form;

use Drupal\Core\Form\FormStateInterface;

final class CollectionItemEditForm extends ModelContentEntityForm {

  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['whiskey']['widget'][0]['target_id']['#disabled'] = TRUE;
    return $form;
  }

}
