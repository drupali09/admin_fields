<?php

/**
 * @file
 * Contains \Drupal\admin_fields\Routing\RouteSubscriber.
 */

namespace Drupal\admin_fields\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for admin_fields routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($route = $this->getEntityLabelRoute($entity_type)) {
        $collection->add("entity.$entity_type_id.admin_fields", $route);
      }
    }
  }

  /**
   * Gets the Manage term permissions per content type route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getEntityLabelRoute(EntityTypeInterface $entity_type) {
    if ($route_load = $entity_type->getLinkTemplate('admin-fields')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($route_load);
      $route
        ->addDefaults([
          '_form' => '\Drupal\admin_fields\Form\SettingsForm',
          '_title' => 'Add admin fields',
        ])
        ->addRequirements([
          '_permission' => 'administer ' . $entity_type_id . ' labels',
        ])
        ->setOption('_admin_route', TRUE)
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);
      return $route;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = array('onAlterRoutes', -100);
    return $events;
  }

}
