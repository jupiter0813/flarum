import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import StatusWidget from 'flarum/admin/components/StatusWidget';
import Button from 'flarum/common/components/Button';
import purgeLSCache from '../common/purgeLSCache';

export default () => {
  extend(StatusWidget.prototype, 'toolsItems', (items) => {
    items.add('clearLSCache', <Button onclick={() => purgeLSCache()}>{app.translator.trans('acpl-lscache.admin.purge_all')}</Button>);
  });
};
