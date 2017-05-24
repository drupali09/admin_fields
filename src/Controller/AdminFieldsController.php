<?php

namespace Drupal\admin_fields\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\Component\Utility\Tags;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Drupal\Component\Utility\Html;
use \Drupal\Core\Access\AccessResult;

/**
 * Default controller for the admin_fields module.
 */
class AdminFieldsController extends ControllerBase {

  /**
   * AdminFieldsController constructor.
   *
   */
  public function __construct(AccessCheckInterface $access_check_service) {

  }

}