<?php

namespace ThemeHouse\Bookmarks\XFRM\Entity;

use ThemeHouse\Bookmarks\Entity\BookmarkableTrait;

/**
 * Class ResourceItem
 * @package ThemeHouse\Bookmarks\XFRM\Entity
 */
class ResourceItem extends XFCP_ResourceItem
{
    use BookmarkableTrait;

    protected function _postDelete()
    {
        parent::_postDelete();

        /** @var \ThemeHouse\Bookmarks\Repository\Bookmark $repo */
        $repo = $this->repository('ThemeHouse\Bookmarks:Bookmark');
        $repo->fastDeleteBookmarksForContent('resource', $this->resource_id);
    }
}