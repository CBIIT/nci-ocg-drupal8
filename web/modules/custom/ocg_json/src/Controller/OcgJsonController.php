<?php

/**
* @file
* Contains \Drupal\ocg_json\Controller\OcgJsonController.
*/
namespace Drupal\ocg_json\Controller;
use Drupal\Core\Controller\ControllerBase;
/**
* Controller routines for hello module routes.
*/
class OcgJsonController extends ControllerBase {
/**
* Return the 'Hello World' page.
*
* @return string
* A render array containing our 'Hello World' page content.
*/
public function content() {
$output = array();
$output['hello'] = array(
'#markup' => '',
);
return $output;
}
}