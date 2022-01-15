import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import StatusWidget from 'flarum/admin/components/StatusWidget';
import ItemList from 'flarum/common/utils/ItemList';
import Button from 'flarum/common/components/Button';

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
  extend(StatusWidget.prototype, 'toolsItems', (items: ItemList) => {

    items.add('clearLSCache', <Button onclick={handleClearLSCache}>{app.translator.trans('acpl-lscache.admin.purge_all')}</Button>)
  });
};
