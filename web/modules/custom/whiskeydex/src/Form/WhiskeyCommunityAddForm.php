<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\whiskeydex\Entity\Whiskey;

final class WhiskeyCommunityAddForm extends ContentEntityForm {

  protected function prepareEntity(): void {
    parent::prepareEntity();
    assert($this->entity instanceof Whiskey);
    if ($this->entity->label() === NULL) {
      $this->entity->set('name', (string) $this->getRequest()->query->get('name'));
    }
    $this->entity->set('community', TRUE);
  }

  /**
   * @phpstan-param array<string, mixed> $form
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = $this->entity->save();

    $this->messenger()->addMessage($this->t('The whiskey %label has been created, add it to your collection.', [
      '%label' => $this->entity->label(),
    ]));
    $form_state->setRedirect('entity.collection_item.add_form', ['whiskey' => $this->entity->id()]);

    return $result;
  }

}
