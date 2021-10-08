<?php

namespace ThemeHouse\Bookmarks\XF\Entity;

use XF\Mvc\Entity\ArrayCollection;

class User extends XFCP_User
{
    public function getLatestBookmarks($contentType, $limit)
    {
        return \XF::finder('ThemeHouse\Bookmarks:Bookmark')
            ->where('user_id', '=', $this->user_id)
            ->where('content_type', '=', $contentType)
            ->order('bookmark_date', 'DESC')
            ->limit($limit)
            ->fetch();
    }

    public function getLatestNodeBookmarks($limit = 5)
    {
        return $this->getLatestBookmarks('node', $limit);
    }

    public function getLatestThreadBookmarks($limit = 5)
    {
        return $this->getLatestBookmarks('thread', $limit);
    }
}