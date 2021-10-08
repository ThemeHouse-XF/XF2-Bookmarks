<?php

namespace ThemeHouse\Bookmarks\XF\Entity;

use ThemeHouse\Bookmarks\Entity\BookmarkableTrait;

/**
 * Class Node
 * @package ThemeHouse\Bookmarks\XF\Entity
 */
class Node extends XFCP_Node
{
    use BookmarkableTrait;

    /**
     * @return bool
     */
    protected function canBookmarkExtra()
    {
        if ($this->node_type_id !== 'Forum') {
            return false;
        }

        return true;
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $repo = $this->repository('ThemeHouse\Bookmarks:Bookmark');
        $repo->fastDeleteBookmarksForContent('node', $this->node_id);
    }
}