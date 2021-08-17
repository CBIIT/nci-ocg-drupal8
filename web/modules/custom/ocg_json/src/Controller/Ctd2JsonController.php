<?php

/**
 * @file
 * Contains \Drupal\ocg_json\Controller\Ctd2JsonController.
 */

namespace Drupal\ocg_json\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller routines for hello module routes.
 */
class Ctd2JsonController extends ControllerBase {

  /**
   * Return the 'Hello World' page.
   *
   * @return string
   * A render array containing our 'Hello World' page content.
   */
  public function data() {

    $json_array = array(
        'nodes' => array()
    );
    $view_results = views_get_view_result('ctd2_data_portal', 'data');
    foreach ($view_results as $view_result) {
      //dump($view_result->nid);
      $nids[] = $view_result->nid;
    }
    $key = 0;
    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      if ($node->get('field_internal')->value == 0) {
        //dump($node);
        $pos = strpos($node->get('field_institute')->uri, 'internal:');
        if($pos !== false) {
          $node->get('field_institute')->uri = str_replace('internal:/', '', $node->get('field_institute')->uri);
        }
        $json_array['nodes'][$key]['node'] = array(
            'id' => $node->get('nid')->value,
            'title' => array(
                'title' => $node->get('field_institute')->title,
                'url' => $node->get('field_institute')->uri,
            ),
            'row_id' => $node->get('field_row')->target_id,
        );
        $row_key = 0;
        foreach ($node->get('field_row') as $row) {
          $p = \Drupal::entityTypeManager()->getStorage('paragraph')->load($row->target_id);
          if ($p->get('field_internal')->value == 0) {
            //dump($p);
            if($p->get('field_row_number')->value){
              $pos = strpos($p->get('field_project_title')->uri, 'internal:');
              if($pos !== false) {
                $p->get('field_project_title')->uri = str_replace('internal:/', '', $p->get('field_project_title')->uri);
              }
            $json_array['nodes'][$key]['node']['row'][$row_key] = array(
                'row_number' => $p->get('field_row_number')->value,
                'submission_date' => $p->get('field_submission_date')->value,
                'project_title' => array(
                    'title' => $p->get('field_project_title')->title,
                    'url' => $p->get('field_project_title')->uri,
                ),
            );
            } else {
              $json_array['nodes'][$key]['node']['row'][$row_key] = array(
                'row_number' => null,
                'submission_date' => $p->get('field_submission_date')->value,
                'project_title' => $p->get('field_project_title')->title,
              );
            }
            foreach ($p->get('field_assay_type') as $field_assay_type_key => $field_assay_type) {
              $t = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_assay_type->target_id);
              //dump($t);
              $json_array['nodes'][$key]['node']['row'][$row_key]['assay_type'][] = array(
                  'name' => $t->get('name')->value,
              );
            }
            foreach ($p->get('field_paper') as $papers_key => $papers) {
              $paper = \Drupal::entityTypeManager()->getStorage('paragraph')->load($papers->target_id);
              $pos = strpos($paper->get('field_paper_link')->uri, 'internal:');
              if($pos !== false) {
                $paper->get('field_paper_link')->uri = str_replace('internal:/', '', $paper->get('field_paper_link')->uri);
              }
              $json_array['nodes'][$key]['node']['row'][$row_key]['paper'][]['paper_link'] = array(
                'url' => $paper->get('field_paper_link')->uri,
                'title' => $paper->get('field_paper_link')->title,   
              );
            }
            foreach ($p->get('field_experimental_approaches') as $approaches_key => $approaches) {
              $approach = \Drupal::entityTypeManager()->getStorage('paragraph')->load($approaches->target_id);
              $pos = strpos($approach->get('field_text')->uri, 'internal:');
              if($pos !== false) {
                $approach->get('field_text')->uri = str_replace('internal:/', '', $approach->get('field_text')->uri);
              }
              $json_array['nodes'][$key]['node']['row'][$row_key]['approaches'][] = array(
                'span_rows' => $approach->get('field_span_rows')->value,
                'field_text' => array(
                  'url' => $approach->get('field_text')->uri,
                  'title' => $approach->get('field_text')->title,
                ),
                   
              );
            }
            foreach ($p->get('field_ctd2_data_project_page') as $dpp_key => $dpp_list) {
              $dpp = \Drupal::entityTypeManager()->getStorage('node')->load($dpp_list->target_id);
              $json_array['nodes'][$key]['node']['row'][$row_key]['dpp'] = array(
                  'title' => $dpp->get('title')->value,
                  'body' => $dpp->get('body')->value,
                );
              foreach ($dpp->get('field_dpp_approaches') as $dpp_approaches){
                $dpp_approach = \Drupal::entityTypeManager()->getStorage('paragraph')->load($dpp_approaches->target_id);
                $json_array['nodes'][$key]['node']['row'][$row_key]['dpp'][] = array(
                  'dpp_title' => $dpp_approach->get('field_dpp_title')->value,
                  'dpp_body' => $dpp_approach->get('field_dpp_body')->value,
                );
              }
            }
            foreach ($p->get('field_data') as $data_key => $data_list) {
              $data = \Drupal::entityTypeManager()->getStorage('paragraph')->load($data_list->target_id);
              $pos = strpos($data->get('field_data_link')->uri, 'internal:');
              if($pos !== false) {
                $data->get('field_data_link')->uri = str_replace('internal:/', '', $data->get('field_data_link')->uri);
              }
              $json_array['nodes'][$key]['node']['row'][$row_key]['data'][] = array(
                'data_link' => array(
                  'url' => $data->get('field_data_link')->uri,
                  'title' => $data->get('field_data_link')->title,
                ),
                   
              );
            }
            foreach ($p->get('field_principal_investigator') as $investigators_key => $investigators) {
              $investigator = \Drupal::entityTypeManager()->getStorage('paragraph')->load($investigators->target_id);
              $json_array['nodes'][$key]['node']['row'][$row_key]['investigator'][] = array(
                'investigator' => $investigator->get('field_pi_link')->value,                   
              );
            }
            foreach ($p->get('field_ctd2_contact_name') as $contacts_key => $contacts) {
              $contact = \Drupal::entityTypeManager()->getStorage('paragraph')->load($contacts->target_id);
              $pos = strpos($contact->get('field_contact_link')->uri, 'internal:');
              if($pos !== false) {
                $contact->get('field_contact_link')->uri = str_replace('internal:/', '', $contact->get('field_contact_link')->uri);
              }
              $json_array['nodes'][$key]['node']['row'][$row_key]['contact_link'][] = array(
                'url' => $contact->get('field_contact_link')->uri,
                'title' => $contact->get('field_contact_link')->title,
              );
            }
            $row_key++;
          }
        }
      }
      $key++;
    }
    return new JsonResponse($json_array);
  }

}
