<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

class ModelContentEntityForm extends ContentEntityForm {

  /**
   * @phpstan-param array<string, mixed> $form
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $created = $this->entity->isNew();
    $result = $this->entity->save();

    $this->messenger()->addMessage($this->t('The %entity_type %label has been %action.', [
      '%entity_type' => $this->entity->getEntityType()->getLabel(),
      '%label' => $this->entity->label(),
      '%action' => $created ? $this->t('created') : $this->t('updated'),
    ]));
    $form_state->setRedirectUrl($this->entity->toUrl('canonical'));

    return $result;
  }

}
