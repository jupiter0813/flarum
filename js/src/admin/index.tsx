import app from 'flarum/admin/app';
import Link from 'flarum/common/components/Link';
import addPurgeLSCacheButton from './addPurgeLSCacheButton';
import { PURGE_ICON } from '../common/constants';

app.initializers.add('acpl-lscache', () => {
  addPurgeLSCacheButton();

  app.extensionData
    .for('acpl-lscache')
    .registerSetting({
      setting: 'acpl-lscache.cache_enabled',
      label: app.translator.trans('acpl-lscache.admin.cache_enabled_label'),
      help: app.translator.trans('acpl-lscache.admin.cache_enabled_help', {
        a: (
          <Link
            href="https://docs.litespeedtech.com/lscache/noplugin/installation/#verify-your-site-is-being-cached"
            external={true}
            target="_blank"
            rel="noopener noreferrer"
          />
        ),
      }),
      type: 'boolean',
    })
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
        a: (
          <Link
            href="https://docs.litespeedtech.com/lscache/devguide/controls/#cache-tag"
            external={true}
            target="_blank"
            rel="noopener noreferrer"
          />
        ),
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
        a: (
          <Link
            href="https://docs.litespeedtech.com/lscache/start/#drop-junk-query-strings"
            external={true}
            target="_blank"
            rel="noopener noreferrer"
          />
        ),
      }),
      type: 'textarea',
    })
    .registerSetting({
      setting: 'acpl-lscache.status_codes_cache',
      label: app.translator.trans('acpl-lscache.admin.status_codes_cache_label'),
      help: app.translator.trans('acpl-lscache.admin.status_codes_cache_help'),
      type: 'textarea',
    })
    .registerPermission(
      {
        icon: PURGE_ICON,
        label: app.translator.trans('acpl-lscache.admin.permissions.purge'),
        permission: 'lscache.purge',
      },
      'moderate'
    );
});
