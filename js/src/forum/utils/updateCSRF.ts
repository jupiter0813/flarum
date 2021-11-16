import app from 'flarum/forum/app';

export default async () => {
  if (app.session.user) {
    return;
  }

  // We do not use `app.request` here because that function returns only the body and we need headers
  const res = await fetch(`${app.forum.attribute('apiUrl')}/lscache-csrf`);
  const token = res.headers.get('X-CSRF-Token');

  if (token) {
    app.session.csrfToken = token;
  }
};
