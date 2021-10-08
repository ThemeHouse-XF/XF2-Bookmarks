<?php

namespace ThemeHouse\Bookmarks\XF\Entity;

use ThemeHouse\Bookmarks\Entity\BookmarkableTrait;

class Thread extends XFCP_Thread
{
    use BookmarkableTrait;

    protected function _postDelete()
    {
        parent::_postDelete();

        /** @var \ThemeHouse\Bookmarks\Repository\Bookmark $repo */
        $repo = $this->repository('ThemeHouse\Bookmarks:Bookmark');
        $repo->fastDeleteBookmarksForContent('thread', $this->thread_id);
    }
}