<?php

namespace ThemeHouse\Bookmarks\XFMG\Entity;

use ThemeHouse\Bookmarks\Entity\BookmarkableTrait;

/**
 * Class MediaItem
 * @package ThemeHouse\Bookmarks\XFMG\Entity
 */
class MediaItem extends XFCP_MediaItem
{
    use BookmarkableTrait;

    protected function _postDelete()
    {
        parent::_postDelete();

        /** @var \ThemeHouse\Bookmarks\Repository\Bookmark $repo */
        $repo = $this->repository('ThemeHouse\Bookmarks:Bookmark');
        $repo->fastDeleteBookmarksForContent('xfmg_media', $this->media_id);
    }
}