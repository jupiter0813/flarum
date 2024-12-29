import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import Button from 'flarum/common/components/Button';
import purgeLSCache from '../common/purgeLSCache';
import { PURGE_ICON } from '../common/constants';

export default function extendDiscussionControls() {
  extend(DiscussionControls, 'moderationControls', (items, discussion) => {
    const discussionId = discussion.id();
    const { user } = app.session;

    if (!discussionId || !user || !user.canPurgeLSCache()) {
      return;
    }

    items.add(
      'acpl-lscache-purge',
      <Button icon={PURGE_ICON} onclick={() => purgeLSCache([`discussion_${discussionId}`])}>
        {app.translator.trans('acpl-lscache.forum.purge.discussion')}
      </Button>
    );
  });
}
