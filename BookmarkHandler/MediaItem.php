<?php

namespace ThemeHouse\Bookmarks\BookmarkHandler;

use ThemeHouse\Bookmarks\Entity\Bookmark;
use XF\Template\Templater;

/**
 * Class MediaItem
 * @package ThemeHouse\Bookmarks\BookmarkHandler
 */
class MediaItem extends AbstractHandler
{
    protected $contentType = 'xfmg_media';


    public function getTitle($content) {
        return $content->title;
    }

    public function getLink($content) {
        return \XF::app()->router()->buildLink('media', $content);
    }
    
    /**
     * @return bool
     */
    public function canUse()
    {
        return isset($this->app->registry()['addOns']['XFMG']);
    }

    /**
     * @return array
     */
    public function getEntityWith()
    {
        return ['User'];
    }

    /**
     * @param Bookmark $bookmark
     * @param $type
     * @param Templater $templater
     * @return bool|mixed|string
     */
    protected function _renderIcon(Bookmark $bookmark, $type, Templater $templater)
    {
        /** @var \XFMG\Entity\MediaItem $media */
        $media = $bookmark->Content;

        return $templater->renderMacro('public:th_bookmark_icon_macros_bookmarks', 'xfmg_media', [
            'media' => $media,
        ]);
    }

    /**
     * @param Bookmark $bookmark
     * @param $type
     * @param array $options
     * @param Templater $templater
     * @return mixed|string
     */
    protected function _renderBookmark(Bookmark $bookmark, $type, Array $options = [], Templater $templater)
    {
        $media = $bookmark->Content;

        $params = [
            'bookmark' => $bookmark,
            'media' => $media,

            'options' => $options,
        ];
        return $templater->renderTemplate('public:th_bookmark_row_xfmg_media_bookmarks', $params);
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function getContentUrl($content)
    {
        return \XF::app()->router()->buildLink('media', $content);
    }

    public function getBookmarkPhrase($content)
    {
        return \XF::phrase('th_bookmark_media_bookmarks');
    }

    public function getUnbookmarkPhrase($content)
    {
        return \XF::phrase('th_unbookmark_media_bookmarks');
    }
}