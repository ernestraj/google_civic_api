<?php
/**  
 * @file  
 * Contains Drupal\om_goca\Form.
 */ 

namespace Drupal\om_goca\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Messenger\MessengerInterface;

class SearchRepresentatives extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_represntatives_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

  	$form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_area">Please Select Address</div>'
    ];
 
    // Create the address field.
    $form['address'] = [
      '#type' => 'address',
      '#default_value' => [
        'country_code' => 'US',
      ],
      '#used_fields' => [
        AddressField::ADDRESS_LINE1,
        AddressField::ADDRESS_LINE2,
        AddressField::ADMINISTRATIVE_AREA,
        AddressField::LOCALITY,
        AddressField::POSTAL_CODE,
      ],
      '#available_countries' => ['US'],
    ];

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::setMessage',
      ],
    ];

    $form['reload'] = [
      '#type' => 'button',
      '#value' => $this->t('Reload'),
      '#attributes' => [
        'class' => ['reload-form']
      ]
    ];

    $form['#attached']['library'][] = 'om_goca/om_goca';

    return $form;

  }

  /**
   *
   */
  public function setMessage(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('om_goca.adminsettings');
    $values = $form_state->getValues();
    $address = $values['address'];

    if (!empty($address['country_code']) && $address['country_code'] === 'US') {
      $address_line = $address['address_line1'] . ' ' . $address['locality'] . ' ' . $address['administrative_area'] . ' ' . $address['postal_code'];

      $key = $config->get('om_goca_api_key');

      $client = new Client(['base_uri' => 'https://content.googleapis.com']);
      $request = $client->get('/civicinfo/v2/representatives', 
        [
          'query' => [
            'address' => $address_line,
            'key' => $key
          ]
        ]
      );

      $result = $request->getBody();

      $code = $request->getStatusCode();

      $public_address = json_decode($result, TRUE);

      foreach ($public_address['officials'] as $item) {
        $full_address = '';
        foreach ($item['address'][0] as $row) {
          if (!empty($row)) {
            $full_address .= $row . ' ';
          }
        }
        if(!empty($item['photoUrl'])) {
          $image_variables = [
            '#theme' => 'image',
            '#uri' => $item['photoUrl'],
            '#width' => '100px',
            '#height' => '100px'
          ];
          $image = \Drupal::service('renderer')->render($image_variables);
        }
        else {
          $image = t('No Image');
        }
        $officials[] = [
          $image,
          $item['name'],
          trim($full_address),
          $item['party'],
          $item['phones'][0],
          $item['urls'][0],
        ];
      }

      $header = [
        t('Photo'),
        t('Name'), 
        t('Address'), 
        t('Party'), 
        t('Phone'), 
        t('URL')
      ];

      $render['political_representatives'] = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $officials,
        '#attributes' => ['class'=>['political_representatives']],
        '#header_columns' => 6,
      ];

      $html = \Drupal::service('renderer')->renderPlain($render);

      $response = new AjaxResponse();
      $response->addCommand(
        new HtmlCommand(
          '.result_area',
          $html
        )
      );
    }
    else {
      $response = new AjaxResponse();
      $response->addCommand(
        new HtmlCommand(
          '.result_area',
          '<div class="my_message">Please select the country code!</div>' //Validation message for validation usecases
        )
      );
    }

    return $response;
  }

  /**
   * Implements a form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      // Nothing to do. Use Ajax.
  }

}