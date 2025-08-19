<?php

declare(strict_types=1);

namespace Drupal\ai_search_header\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the AI Search Header Block.
 *
 * @Block(
 *   id = "ai_search_header_block",
 *   admin_label = @Translation("AI Search Header")
 * )
 */
class AiSearchHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected FormBuilderInterface $formBuilder,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
    );
  }

  public function defaultConfiguration(): array {
    return [
        'destination_path' => '/basic-page/fancy-search',
      ] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state): array {
    $form['destination_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Destination path'),
      '#description' => $this->t('Path that contains the AI Search block (e.g. /basic-page/fancy-search).'),
      '#default_value' => $this->configuration['destination_path'],
      '#required' => TRUE,
    ];
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['destination_path'] = '/' . ltrim((string) $form_state->getValue('destination_path'), '/');
  }

  public function build(): array {
    $destination = $this->configuration['destination_path'] ?: '/basic-page/fancy-search';

    // Build the header search form and pass destination into it.
    $form = $this->formBuilder->getForm(
      \Drupal\ai_search_header\Form\AiSearchHeaderForm::class,
      $destination
    );

    // Attach autorun JS (no JS changes needed).
    $form['#attached']['library'][] = 'ai_search_header/autorun';

    // Wrap like core search block: block wrapper + single content.container-inline.
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'search-block-form',       // matches core block wrapper
          '_none',
          'block',
          'block-search',
          'block-search-form-block',
        ],
        'data-drupal-selector' => 'search-block-form',
        'role' => 'search',
      ],
      'content' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['content', 'container-inline'],
        ],
        'form' => $form,
      ],
      '#cache' => [
        'contexts' => ['url.path', 'user.roles'],
      ],
    ];

    return $build;
  }
}
