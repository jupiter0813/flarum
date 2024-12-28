import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import Button from 'flarum/common/components/Button';
import purgeLSCache from '../common/purgeLSCache';

export default function extendDiscussionControls() {
  extend(DiscussionControls, 'moderationControls', (items, discussion) => {
    const discussionId = discussion.id();
    const { user } = app.session;

    if (!discussionId || !user || !user.canPurgeLSCache()) {
      return;
    }

    items.add(
      'acpl-lscache-purge',
      <Button icon="fas fa-broom" onclick={() => purgeLSCache([`discussion_${discussionId}`])}>
        {app.translator.trans('acpl-lscache.forum.purge.discussion')}
      </Button>
    );
  });
}
