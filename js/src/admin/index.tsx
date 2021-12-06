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
      setting: 'acpl-lscache.serve_stale',
      label: app.translator.trans('acpl-lscache.admin.serve_stale_label'),
      help: app.translator.trans('acpl-lscache.admin.serve_stale_help'),
      type: 'boolean',
    })
    .registerSetting(function () {
      return (
        <div className="Form-group">
          <label htmlFor="purge_link_list">{app.translator.trans('acpl-lscache.admin.purge_on_discussion_update_label')}</label>
          <div className="helpText">
            {app.translator.trans('acpl-lscache.admin.purge_on_discussion_update_help', {
              a: <Link href="https://docs.litespeedtech.com/lscache/devguide/controls/#cache-tag" external={true} target="_blank" />,
            })}
          </div>
          <textarea
            id="purge_link_list"
            className="FormControl"
            rows={4}
            bidi={
              //@ts-ignore
              this.setting('acpl-lscache.purge_on_discussion_update')
            }
          />
        </div>
      );
    })
    .registerSetting(function () {
      return (
        <div className="Form-group">
          <label htmlFor="exclude_link_list">{app.translator.trans('acpl-lscache.admin.cache_exclude_label')}</label>
          <div className="helpText">{app.translator.trans('acpl-lscache.admin.cache_exclude_help')}</div>
          <textarea
            id="exclude_link_list"
            className="FormControl"
            rows={4}
            bidi={
              //@ts-ignore
              this.setting('acpl-lscache.cache_exclude')
            }
          />
        </div>
      );
    });

  addPurgeLSCacheButton();
});
