import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import StatusWidget from 'flarum/admin/components/StatusWidget';
import Button from 'flarum/common/components/Button';
import type ItemList from 'flarum/common/utils/ItemList';
import type { Children } from 'mithril';

function handleClearLSCache() {
  app
    .request({
      url: `${app.forum.attribute('apiUrl')}/lscache-purge`,
      method: 'GET',
    })
    .then(() => {
      app.alerts.show({ type: 'success' }, app.translator.trans('acpl-lscache.admin.purge_all_success'));
    });
}

export default () => {
  extend(StatusWidget.prototype, 'toolsItems', (items: ItemList<Children>) => {
    items.add('clearLSCache', <Button onclick={handleClearLSCache}>{app.translator.trans('acpl-lscache.admin.purge_all')}</Button>);
  });
};
