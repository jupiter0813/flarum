import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Modal from 'flarum/common/components/Modal';
import updateCSRF from './utils/updateCSRF';
import extendDiscussionControls from './extendDiscussionControls';

export { default as extend } from './extend';

app.initializers.add('acpl-lscache', () => {
  // We extend each modal to also include those added by external extensions
  extend(Modal.prototype, 'oninit', updateCSRF);
  extendDiscussionControls();
});
