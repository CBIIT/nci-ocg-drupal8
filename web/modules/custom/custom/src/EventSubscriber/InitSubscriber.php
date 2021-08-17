<?php /**
 * @file
 * Contains \Drupal\custom\EventSubscriber\InitSubscriber.
 */

namespace Drupal\custom\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  public function onEvent() {
    //$plugin = context_get_plugin('conditions', 'status');
    //if ($plugin) {
      //$plugin->execute();
    //}
  }

}
