<?php

namespace Drupal\webform_ga4_handler\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Pushes webform data layer variables on submission success.
 *
 * @WebformHandler(
 * id = "ga4_data_layer",
 * label = @Translation("GA4 Data Layer Handler"),
 * category = @Translation("External"),
 * description = @Translation("Adds JavaScript to push submission data to GTM dataLayer."),
 * cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 * results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 * )
 */
class Ga4DataLayerHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $is_new = TRUE) {
    // Only run on new submissions
    if ($is_new) {
      $data_layer_variables = [
        'event' => 'generate_lead',
        'form_name' => $webform_submission->getWebform()->label(),
        // Use Webform's token service to get field values
        'source' => $webform_submission->getTokenData('webform_submission', 'utm_source'),
        'medium' => $webform_submission->getTokenData('webform_submission', 'utm_medium'),
        'project_type' => $webform_submission->getTokenData('webform_submission', 'project_type'),
      ];

      $data_layer_json = json_encode($data_layer_variables);

      // Build the inline script to push the data to the Data Layer
      $script = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => 'dataLayer.push(' . $data_layer_json . ');',
        '#attributes' => ['type' => 'text/javascript'],
        '#weight' => 99999,
      ];

      // Attach the script to the current page render array (the Confirmation page)
      \Drupal::service('renderer')->renderRoot($script);
      \Drupal::service('page_attachments')->renderAttachments([$script]);
    }
  }
}