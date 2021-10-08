<?php

namespace ThemeHouse\Bookmarks\XF\Entity;

use ThemeHouse\Bookmarks\Entity\BookmarkableTrait;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class Post
 * @package ThemeHouse\Bookmarks\XF\Entity
 */
class Post extends XFCP_Post
{
    use BookmarkableTrait;

    /**
     * @return bool
     */
    protected function canBookmarkExtra()
    {
        if ($this->isFirstPost()) {
            return false;
        }

        return true;
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        /** @var \ThemeHouse\Bookmarks\Repository\Bookmark $repo */
        $repo = $this->repository('ThemeHouse\Bookmarks:Bookmark');
        $repo->fastDeleteBookmarksForContent('post', $this->post_id);
    }
}