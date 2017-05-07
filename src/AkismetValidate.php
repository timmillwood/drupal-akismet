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
  public static function validate(array &$form, FormStateInterface $form_state) {
    $akismet = new Akismet(self::getKey(), self::getUrl());
    if ($akismet->verifyKey()) {
      //@todo Support more than just the first comment_body field value.
      $comment = $form_state->getValue('comment_body')[0]['value'];
      if ($akismet->isSpam($comment)) {
        $form_state->setError($form, 'Looks like spam');
      }
    }
    else {
      $form_state->setError($form, 'Invalid key');
    }
  }

  /**
   * @return string
   */
  private static function getKey() {
    return \Drupal::service('settings')->get('akismet_key');
  }

  /**
   * @return string
   */
  private static function getUrl() {
    return \Drupal::request()->getSchemeAndHttpHost();
  }

}
