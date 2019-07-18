<?php
/**  
 * @file  
 * Contains Drupal\om_goca\Form\OMGOCAAdminForm.  
 */  
namespace Drupal\om_goca\Form;  
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class OMGOCAAdminForm extends ConfigFormBase {

  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'om_goca.adminsettings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'om_goca_admin_form';  
  }

  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('om_goca.adminsettings');  

    $form['om_goca_api_key'] = [  
      '#type' => 'textfield',  
      '#title' => $this->t('Provide API Key'),  
      '#description' => $this->t("Please provide API key for the google project and enable Google Civic API for the project."),  
      '#default_value' => "",  
    ];  

    return parent::buildForm($form, $form_state);  
  }

  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    parent::submitForm($form, $form_state);  

    $this->config('om_goca.adminsettings')  
      ->set('om_goca_api_key', $form_state->getValue('om_goca_api_key'))  
      ->save();  
  }   

}