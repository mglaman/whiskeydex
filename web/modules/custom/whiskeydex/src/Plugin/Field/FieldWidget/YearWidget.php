<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "number_year",
 *   label = @Translation("Year field"),
 *   field_types = {
 *     "integer",
 *   }
 * )
 */
final class YearWidget extends WidgetBase {

  /**
   * @phpstan-param \Drupal\Core\Field\FieldItemListInterface<\Drupal\Core\Field\Plugin\DataType\FieldItem> $items
   * @phpstan-param array<string, mixed> $element
   * @phpstan-param array<string, mixed> $form
   *
   * @phpstan-return array<string, mixed>
   *
   * @phpstan-ignore-next-line
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $value = $items[$delta]->value ?? NULL;
    $element += [
      '#type' => 'textfield',
      '#default_value' => $value,
      '#pattern' => '\d{4}',
      '#maxlength' => 4,
      '#attributes' => [
        'minlength' => 4,
        'inputmode' => 'numeric',
      ],
    ];
    return ['value' => $element];
  }

}
