<?php

namespace Drupal\smart_sql_idmap\Plugin\migrate\id_map;

use Drupal\Component\Plugin\PluginBase;
use Drupal\migrate\Plugin\migrate\id_map\Sql;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A smart, sql based ID map.
 *
 * TODO provide an upgrade path when https://drupal.org/i/2845340 gets fixed.
 *
 * @PluginID("smart_sql")
 */
class SmartSql extends Sql {

  /**
   * Constructs an SQL object.
   *
   * Sets up the tables and builds the maps,
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin ID for the migration process to do.
   * @param mixed $plugin_definition
   *   The configuration for the plugin.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration to do.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EventDispatcherInterface $event_dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $event_dispatcher);

    // Default generated table names, limited to 63 characters.
    $machine_name = str_replace(PluginBase::DERIVATIVE_SEPARATOR, '__', $this->migration->id());
    $prefix_length = strlen($this->database->tablePrefix());

    $map_table_name = 'm_map_' . mb_strtolower($machine_name);
    $this->mapTableName = mb_substr($map_table_name, 0, 63 - $prefix_length) === $map_table_name
      ? $map_table_name
      : mb_substr($map_table_name, 0, 45 - $prefix_length) . '_' . substr(md5($machine_name), 0, 17);

    $message_table_name = 'm_message_' . mb_strtolower($machine_name);
    $this->messageTableName = mb_substr($message_table_name, 0, 63 - $prefix_length) === $message_table_name
      ? $message_table_name
      : mb_substr($message_table_name, 0, 45 - $prefix_length) . '_' . substr(md5($machine_name), 0, 17);
  }

}
