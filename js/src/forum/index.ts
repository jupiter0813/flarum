import app from 'flarum/forum/app';
// import { extend } from 'flarum/common/extend';
// import LogInModal from 'flarum/forum/components/LogInModal';
// import SignUpModal from 'flarum/forum/components/SignUpModal';
import updateCSRF from './utils/updateCSRF';

app.initializers.add('acpl-lscache', () => {
  // [LogInModal, SignUpModal].forEach((Component) => {
  //   extend(Component.prototype, 'oninit', updateCSRF);
  // });
  updateCSRF();
});
