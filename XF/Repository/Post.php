<?php

namespace ThemeHouse\Bookmarks\XF\Repository;

use XF\Entity\Thread;

/**
 * Class Post
 * @package ThemeHouse\Bookmarks\XF\Repository
 */
class Post extends XFCP_Post
{
    public function findPostsForThreadView(Thread $thread, array $limits = [])
    {
        $finder = parent::findPostsForThreadView($thread, $limits);

        $finder->with('Bookmark');

        return $finder;
    }

}