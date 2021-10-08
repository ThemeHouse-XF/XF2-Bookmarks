<?php

namespace ThemeHouse\Bookmarks\Import\Data;

use XF\Import\Data\AbstractEmulatedData;

/**
 * Class Bookmark
 * @package ThemeHouse\Bookmarks\Import\Data
 */
class Bookmark extends AbstractEmulatedData
{
    /**
     * @return string
     */
    public function getImportType()
    {
        return 'th_bookmark';
    }

    /**
     * @return string
     */
    public function getEntityShortName()
    {
        return 'ThemeHouse\Bookmarks:Bookmark';
    }

    /**
     * @param $oldId
     * @return null|void
     */
    protected function preSave($oldId)
    {
        if ($this->content_type === 'post') {
            $post = $this->em()->find('XF:Post', $this->content_id);

            if ($post && $post->position === 0) {
                $this->set('content_type', 'thread');
                $this->set('content_id', $post->thread_id);
            }
        }
        parent::preSave($oldId);
    }
}