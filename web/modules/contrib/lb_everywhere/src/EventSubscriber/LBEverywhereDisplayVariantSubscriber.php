<?php

namespace Drupal\lb_everywhere\EventSubscriber;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Selects the Layout Builder Everywhere display variant.
 *
 * @see \Drupal\lb_everywhere\Plugin\DisplayVariant\LBEverywhereDisplayVariant
 */
class LBEverywhereDisplayVariantSubscriber implements EventSubscriberInterface {

  /**
   * Selects the display variant.
   *
   * @param \Drupal\Core\Render\PageDisplayVariantSelectionEvent $event
   *   The event to process.
   */
  public function onSelectPageDisplayVariant(PageDisplayVariantSelectionEvent $event) {
    $event->setPluginId('lb_everywhere');
    $event->stopPropagation();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RenderEvents::SELECT_PAGE_DISPLAY_VARIANT][] = ['onSelectPageDisplayVariant', 100];
    return $events;
  }

}
