<?php

namespace ThemeHouse\Bookmarks\Entity;

/**
 * Trait BookmarkableTrait
 * @package ThemeHouse\Bookmarks\Entity
 */
trait BookmarkableTrait
{
    /**
     * @return bool
     */
    public function canBookmark()
    {
        $options = \XF::options();
        $visitor = \XF::visitor();

        if (!$visitor->user_id) {
            return false;
        }

        if (!$visitor->hasPermission('th_bookmarks', 'canBookmark')) {
            return false;
        }

        if (!in_array($this->getEntityContentType(), $options->th_enableContentTypes_bookmarks)) {
            return false;
        }

        $maxBookmarks = $visitor->hasPermission('th_bookmarks', 'maxBookmarks');
        $bookmarkCount = $visitor->Bookmarks->count();
        if($maxBookmarks >= 0) {
            if($maxBookmarks <= $bookmarkCount) {
                return false;
            }
        }

        return $this->canBookmarkExtra();
    }

    /**
     * @return bool
     */
    protected function canBookmarkExtra()
    {
        return true;
    }
}