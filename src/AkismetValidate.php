<?php

namespace Drupal\akismet;

use Drupal\Core\Form\FormStateInterface;
use TijsVerkoyen\Akismet\Akismet;

/**
 * Class AkismetValidate
 *
 * @internal
 *   This should be only called by the validate handler added in akismet.module.
 */
Class AkismetValidate {

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $akismet = new Akismet($this->getKey(), $this->getUrl());
    if ($akismet->verifyKey()) {
      $entity = $form_state->getFormObject()->getEntity();
      $definitions = \Drupal::entityManager()->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
      foreach ($definitions as $definition) {
        $value = $form_state->getValue($definition->getName());
        if (in_array($definition->getType(), ['text_long', 'text_with_summary']) && isset($value)) {
          if ($akismet->isSpam($value)) {
            $form_state->setError($form, 'Looks like spam');
          }
        }
      }
    }
    else {
      $form_state->setError($form, 'Invalid key');
    }
  }

  /**
   * @return string
   */
  private function getKey() {
    return \Drupal::service('settings')->get('akismet_key');
  }

  /**
   * @return string
   */
  private function getUrl() {
    return \Drupal::request()->getSchemeAndHttpHost();
  }

}
