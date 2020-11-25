# SMART SQL ID MAP

This module provides a work-around for [\[#2845340\]][1]. It contains an
`id_map` migration plugin which can be used even for migrations with very long
plugin ID (e.g. derived migrations).

If you have a migration (plugin) thats migrate map or migrate message DB table's
name is truncated (see [\[#2845340\]][1]), then you should use the ID map plugin
`smart_sql` instead of core's default `sql`.


## Usage

You only have to add this to your migration plugin:

```yaml
idMap:
  plugin: smart_sql
```

So, at the end, you should have something like this:
```yaml
id: d7_tracker_settings
label: Tracker settings
migration_tags:
  - Drupal 7
  - Configuration
idMap:
  plugin: smart_sql
source:
  plugin: variable
  variables_required:
    - tracker_batch_size
process:
  cron_index_limit: tracker_batch_size
destination:
  plugin: config
  config_name: tracker.settings
```

*I will mark it as obsolete when every supported Drupal 8|9 core version will
contain the fix for the issue [\[#2845340\]][1].*

[1]: https://drupal.org/i/2845340
