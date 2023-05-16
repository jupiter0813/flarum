import app from 'flarum/admin/app';
import Link from 'flarum/common/components/Link';
import addPurgeLSCacheButton from './addPurgeLSCacheButton';

app.initializers.add('acpl-lscache', () => {
  app.extensionData
    .for('acpl-lscache')
    .registerSetting({
      setting: 'acpl-lscache.public_cache_ttl',
      label: app.translator.trans('acpl-lscache.admin.public_cache_ttl_label'),
      help: app.translator.trans('acpl-lscache.admin.public_cache_ttl_help'),
      type: 'number',
      min: 30,
    })
    .registerSetting({
      setting: 'acpl-lscache.clearing_cache_listener',
      label: app.translator.trans('acpl-lscache.admin.clearing_cache_listener_label'),
      type: 'boolean',
    })
    .registerSetting({
      setting: 'acpl-lscache.serve_stale',
      label: app.translator.trans('acpl-lscache.admin.serve_stale_label'),
      help: app.translator.trans('acpl-lscache.admin.serve_stale_help'),
      type: 'boolean',
    })
    .registerSetting({
      setting: 'acpl-lscache.purge_on_discussion_update',
      label: app.translator.trans('acpl-lscache.admin.purge_on_discussion_update_label'),
      help: app.translator.trans('acpl-lscache.admin.purge_on_discussion_update_help', {
        a: <Link href="https://docs.litespeedtech.com/lscache/devguide/controls/#cache-tag" external={true} target="_blank" />,
      }),
      type: 'textarea',
    })
    .registerSetting({
      setting: 'acpl-lscache.cache_exclude',
      label: app.translator.trans('acpl-lscache.admin.cache_exclude_label'),
      help: app.translator.trans('acpl-lscache.admin.cache_exclude_help'),
      type: 'textarea',
    })
    .registerSetting({
      setting: 'acpl-lscache.drop_qs',
      label: app.translator.trans('acpl-lscache.admin.drop_qs_label'),
      help: app.translator.trans('acpl-lscache.admin.drop_qs_help', {
        a: <Link href="https://docs.litespeedtech.com/lscache/start/#drop-junk-query-strings" external={true} target="_blank" />,
      }),
      type: 'textarea',
    });

  addPurgeLSCacheButton();
});
