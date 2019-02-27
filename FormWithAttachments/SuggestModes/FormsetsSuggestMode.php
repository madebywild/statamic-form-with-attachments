<?php

namespace Statamic\Addons\FormWithAttachments\SuggestModes;

use Statamic\API\Form;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class FormsetsSuggestMode extends AbstractMode
{
  public function suggestions()
  {
    $suggestions = [];

    $forms = Form::getAllFormsets();

    foreach ($forms as $form) {
      array_push($suggestions, ['value' => $form['name'], 'text' => ucfirst($form['name'])]);
    }
    return $suggestions;
  }
}
