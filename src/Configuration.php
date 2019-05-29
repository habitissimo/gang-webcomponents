<?php
declare(strict_types=1);

namespace Gang\WebComponents;


class Configuration
{
  static $library_cache_driver = null;
  static $library_cache_life_time = 0;
  static $library_base_namespace = "";
  static $library_template_dir = "";

  static $twig_cache_path = null;

  static $log_enable = false;
  static $log_level_warning= true;
  static $log_level_info = true;
  static $log_level_performance = true;

  static $log_allow_create_file = false;
  static $log_file_error_path = "";
}
