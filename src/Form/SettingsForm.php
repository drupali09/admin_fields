<?php

namespace Drupal\admin_fields\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class SettingsForm.
 *
 * @property \Drupal\Core\Config\ConfigFactoryInterface config_factory
 * @property \Drupal\Core\Entity\EntityTypeManagerInterface entity_manager
 * @property  String entity_type_parameter
 * @property  String entity_type_id
 * @package Drupal\admin_fields\Controller
 */
class SettingsForm extends ConfigFormBase {
  protected $configFactory;

  protected $route_match;
  /**
   * {@inheritdoc}
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   */
  public function __construct(ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    parent::__construct($config_factory);
    $this->route_match = $route_match;
    $route_options = $this->route_match->getRouteObject()->getOptions();
    $array_keys = array_keys($route_options['parameters']);
    $this->entity_type_parameter = array_shift($array_keys);
    $entity_type = $this->route_match->getParameter($this->entity_type_parameter);
    $this->entity_type_id = $entity_type->id();
    $this->entity_type_provider =  $entity_type->getEntityType()->getProvider();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'admin_fields.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_fields_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    kint(\Drupal::entityQuery('node')->condition('type','article')->execute());
    $config = $this->config('admin_fields.settings');
    $admin_fields_track = $config->get('admin_fields_track');

    $content_type = $this->entity_type_id;
    $disabled = False;
    $nids = \Drupal::entityQuery('node')->condition('type', $content_type)->execute();
    if (in_array($this->entity_type_id, $admin_fields_track) && count($nids)) {
      $disabled = True;
    }
    $form[$content_type] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable admin fields'),
      '#default_value' => in_array($this->entity_type_id, $admin_fields_track),
      '#disabled' => $disabled,
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('admin_fields.settings');
    $admin_fields_track = $config->get('admin_fields_track');

    foreach ($form_state->getValues() as $key => $value) {
      if ($key == $this->entity_type_id) {
        if ($value == 1 && !in_array($this->entity_type_id, $admin_fields_track)) {
          $admin_fields_track[] = $this->entity_type_id;
          admin_fields_add_slug_field($this->entity_type_id, $label = 'Slug');
          admin_fields_add_textarea_field($this->entity_type_id, 'Note', 'note');
          admin_fields_add_textarea_field($this->entity_type_id, 'Contact', 'contact');
          admin_fields_add_date_field($this->entity_type_id, $label = 'Date de rappel', 'rappel');
        }
      }
    }
    $config->set('admin_fields_track', $admin_fields_track)->save();
    parent::submitForm($form, $form_state);
  }

}
