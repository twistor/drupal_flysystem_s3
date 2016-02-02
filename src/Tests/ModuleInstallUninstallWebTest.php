<?php

/**
 * @file
 * Contains \Drupal\flysystem_s3\Tests\ModuleInstallUninstallWebTest.
 */

namespace Drupal\flysystem_s3\Tests;

use Drupal\flysystem\Tests\ModuleInstallUninstallWebTest as Base;

/**
 * Tests module installation and uninstallation.
 *
 * @group flysystem_s3
 */
class ModuleInstallUninstallWebTest extends Base {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['flysystem_s3'];

}
