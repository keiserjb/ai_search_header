<?php

declare(strict_types=1);

namespace Drupal\ai_search_header\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AiSearchHeaderForm extends FormBase {

  public function getFormId(): string {
    return 'ai_search_header_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?string $destination_path = null): array {
    $form['#method'] = 'post';
    unset($form['#action']); // post to current page

    // Match core search form classes (visuals).
    $form['#attributes']['class'] = [
      'search-form',
      'search-block-form',
      'form-row',
    ];

    $form['destination_path'] = [
      '#type' => 'hidden',
      '#value' => $destination_path ?: '/basic-page/fancy-search',
    ];

    // Search input: keep name "q" for JS capture, give core classes for look.
    $form['q'] = [
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
      '#attributes' => [
        'class' => ['form-search', 'form-control'],
        'aria-label' => $this->t('Search'),
        'title' => $this->t('Enter the terms you wish to search for.'),
        'maxlength' => 128,
        'size' => 15,
      ],
      '#required' => TRUE,
      '#wrapper_attributes' => [
        'class' => ['mb-3', 'form-no-label'],
      ],
    ];

    // Actions container: matches core.
    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'edit-actions',
        'data-drupal-selector' => 'edit-actions',
        'class' => ['form-actions', 'js-form-wrapper', 'form-wrapper', 'mb-3'],
      ],
    ];

    // Submit button: matches core (with tiny seam fix).
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => [
        'data-drupal-selector' => 'edit-submit',
        'class' => [
          'button',
          'js-form-submit',
          'form-submit',
          'btn',
          'btn-primary',
        ],
        'style' => 'margin-left:-5px;', // seam fix
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $dest = (string) $form_state->getValue('destination_path') ?: '/basic-page/fancy-search';
    $form_state->setRedirectUrl(Url::fromUserInput('/' . ltrim($dest, '/')));
  }
}
