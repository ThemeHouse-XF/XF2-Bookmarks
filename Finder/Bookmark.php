<?php

namespace ThemeHouse\Bookmarks\Finder;

use XF\Mvc\Entity\Finder;

/**
 * Class Bookmark
 * @package ThemeHouse\Bookmarks\Finder
 */
class Bookmark extends Finder
{
    /**
     * @param $userId
     * @return $this
     */
    public function user($userId)
    {
        $this->where('user_id', '=', $userId);

        return $this;
    }

    /**
     * @return $this
     */
    public function isPublic()
    {
        $this->where('public', '=', true);

        return $this;
    }

    /**
     * @return $this
     */
    public function sticky()
    {
        $this->recent();
        $this->where('sticky', '=', true);

        return $this;
    }

    /**
     * @return $this
     */
    public function recent()
    {
        $this->order('sticky', 'desc');
        $this->order('bookmark_date', 'desc');

        return $this;
    }

    /**
     * @param $contentType
     * @return $this
     */
    public function forContentType($contentType)
    {
        if ($contentType) {
            $this->where('content_type', '=', $contentType);
        }

        return $this;
    }
}