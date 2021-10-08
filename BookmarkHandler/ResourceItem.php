<?php

namespace ThemeHouse\Bookmarks\BookmarkHandler;

use ThemeHouse\Bookmarks\Entity\Bookmark;
use XF\Template\Templater;

/**
 * Class ResourceItem
 * @package ThemeHouse\Bookmarks\BookmarkHandler
 */
class ResourceItem extends AbstractHandler
{
    protected $contentType = 'resource';

    public function getTitle($content) {
        return $content->title;
    }

    public function getLink($content) {
        return \XF::app()->router()->buildLink('resources', $content);
    }

    /**
     * @return bool
     */
    public function canUse()
    {
        return isset($this->app->registry()['addOns']['XFRM']);
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
        /** @var \XFRM\Entity\ResourceItem $resource */
        $resource = $bookmark->Content;

        return $templater->renderMacro('public:th_bookmark_icon_macros_bookmarks', 'resource', [
            'resource' => $resource,
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
        $resource = $bookmark->Content;

        $params = [
            'bookmark' => $bookmark,
            'resource' => $resource,
            'category' => $resource->Category,

            'options' => $options,
        ];
        return $templater->renderTemplate('public:th_bookmark_row_resource_bookmarks', $params);
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function getContentUrl($content)
    {
        return \XF::app()->router()->buildLink('resources', $content);
    }

    /**
     * @param $content
     * @return \XF\Phrase
     */
    public function getBookmarkPhrase($content)
    {
        return \XF::phrase('th_bookmark_resource_bookmarks');
    }

    /**
     * @param $content
     * @return \XF\Phrase
     */
    public function getUnbookmarkPhrase($content)
    {
        return \XF::phrase('th_unbookmark_resource_bookmarks');
    }
}