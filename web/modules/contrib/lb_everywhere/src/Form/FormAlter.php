<?php

namespace Drupal\lb_everywhere\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\lb_everywhere\LBEverywhereRegionRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alters forms, delegated by hook_form_alter() implementations.
 */
class FormAlter implements ContainerInjectionInterface {

  use AjaxHelperTrait;
  use LayoutRebuildTrait;
  use StringTranslationTrait;

  /**
   * The region repository.
   *
   * @var \Drupal\lb_everywhere\LBEverywhereRegionRepository
   */
  protected $regionRepository;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new FormAlter.
   *
   * @param \Drupal\lb_everywhere\LBEverywhereRegionRepository $region_repository
   *   The region repository.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(LBEverywhereRegionRepository $region_repository, RouteMatchInterface $route_match) {
    $this->regionRepository = $region_repository;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lb_everywhere.region_repository'),
      $container->get('current_route_match')
    );
  }

  /**
   * Alters the layout form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function alterLayoutForm(array &$form, FormStateInterface $form_state) {
    // Remove the message about editing defaults vs overrides.
    unset($form['layout_builder_message']);

    $form['actions']['discard_changes'] = [
      '#type' => 'link',
      '#title' => $this->t('Discard changes'),
      '#url' => $form_state->getFormObject()->getSectionStorage()->getLayoutBuilderUrl('discard_changes'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'width' => 700,
        ]),
      ],
    ];
  }

  /**
   * Alters the discard changes form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function alterDiscardChangesForm(array &$form, FormStateInterface $form_state) {
    if ($this->isAjax()) {
      $form['actions']['submit']['#ajax']['callback'] = [$this, 'ajaxSubmitDiscardChangesForm'];
    }
  }

  /**
   * Submit form dialog #ajax callback.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that rebuilds the layout and closes the modal.
   */
  public function ajaxSubmitDiscardChangesForm(array $form, FormStateInterface $form_state) {
    list($theme, $region) = explode('__', $this->routeMatch->getParameter('storage_id'));
    $cacheable_metadata = new CacheableMetadata();
    $section_storage = $this->regionRepository->getSectionStorage($theme, $region, $cacheable_metadata);

    $response = $this->rebuildLayout($section_storage);
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
