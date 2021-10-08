<?php

namespace ThemeHouse\Bookmarks\BookmarkHandler;

use ThemeHouse\Bookmarks\Entity\Bookmark;
use XF\Template\Templater;

/**
 * Class Post
 * @package ThemeHouse\Bookmarks\BookmarkHandler
 */
class Post extends AbstractHandler
{
    protected $contentType = 'post';

    public function getTitle($content) {
        return $content->Thread->title;
    }

    public function getLink($content) {
        return \XF::app()->router()->buildLink('posts', $content);
    }

    /**
     * @return array
     */
    public function getEntityWith()
    {
        $visitor = \XF::visitor();

        return ['User', 'Thread', 'Thread.Forum', 'Thread.Forum.Node.Permissions|' . $visitor->permission_combination_id];
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
        $params = [
            'bookmark' => $bookmark,
            'post' => $bookmark->Content,
            'thread' => $bookmark->Content->Thread,
            'forum' => $bookmark->Content->Thread->Forum,

            'options' => $options,
        ];
        return $templater->renderTemplate('public:th_bookmark_row_post_bookmarks', $params);
    }

    /**
     * @param $content
     * @param Templater $templater
     * @return bool|string
     */
    protected function _renderExtraCreateFields($content, Templater $templater)
    {
        return $this->_renderExtraEditFields($content, $templater);
    }

    /**
     * @param $content
     * @param Templater $templater
     * @return bool|string
     */
    protected function _renderExtraEditFields($content, Templater $templater)
    {
        $thread = $content->Thread;
        $visitor = \XF::visitor();

        $watch = $thread->Watch[$visitor->user_id];

        $params = [
            'bookmark' => $content->Bookmark,

            'content' => $content,
            'thread' => $thread,
            'forum' => $content->Thread->Forum,

            'watch' => $watch,
        ];

        return $templater->renderTemplate('public:th_bookmark_edit_postFields_bookmarks', $params);
    }

    /**
     * @param $content
     * @param Templater $templater
     * @return bool|string
     */
    protected function _renderExtraDeleteFields($content, Templater $templater)
    {
        $thread = $content->Thread;
        $visitor = \XF::visitor();

        $watch = $thread->Watch[$visitor->user_id];

        $params = [
            'bookmark' => $content->Bookmark,

            'content' => $content,
            'thread' => $thread,
            'forum' => $content->Thread->Forum,

            'watch' => $watch,
        ];

        return $templater->renderTemplate('public:th_bookmark_delete_postFields_bookmarks', $params);
    }

    /**
     * @param Bookmark $bookmark
     * @return bool|void
     */
    protected function _postCreateBookmark(Bookmark $bookmark)
    {
        $this->_postUpdateBookmark($bookmark);
    }

    /**
     * @param Bookmark $bookmark
     * @return bool|void
     */
    protected function _postUpdateBookmark(Bookmark $bookmark)
    {
        /** @var \XF\Entity\Post $post */
        $post = $bookmark->Content;

        /** @var \XF\Entity\Thread $thread */
        $thread = $post->Thread;

        $watchThread = $this->app->request()->filter('watch_thread', 'bool');
        $emailSubscribe = $this->app->request()->filter('email_subscribe', 'bool');

        if ($watchThread) {
            if ($emailSubscribe) {
                $newState = 'watch_email';
            } else {
                $newState = 'watch_no_email';
            }
        } else {
            $newState = 'delete';
        }

        /** @var \XF\Repository\ThreadWatch $watchRepo */
        $watchRepo = $this->repository('XF:ThreadWatch');
        $watchRepo->setWatchState($thread, \XF::visitor(), $newState);
    }

    /**
     * @param Bookmark $bookmark
     * @return bool|void
     */
    protected function _postDeleteBookmark(Bookmark $bookmark)
    {
        /** @var \XF\Entity\Post $post */
        $post = $bookmark->Content;

        /** @var \XF\Entity\Thread $thread */
        $thread = $post->Thread;

        $unwatchThread = $this->app->request()->filter('unwatch_thread', 'bool');

        if ($unwatchThread) {
            $newState = 'delete';

            /** @var \XF\Repository\ThreadWatch $watchRepo */
            $watchRepo = $this->repository('XF:ThreadWatch');
            $watchRepo->setWatchState($thread, \XF::visitor(), $newState);
        }
    }

    /**
     * @param Bookmark $bookmark
     * @param $type
     * @param Templater $templater
     * @return bool|string
     */
    protected function _renderIcon(Bookmark $bookmark, $type, Templater $templater)
    {
        return $templater->fnAvatar($templater, $escape, $bookmark->Content->User, 's');
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function getContentUrl($content)
    {
        return $this->app->router()->buildLink('posts', $content);
    }

    /**
     * @param $content
     * @return \XF\Phrase
     */
    public function getBookmarkPhrase($content)
    {
        return \XF::phrase('th_bookmark_post_bookmarks');
    }

    /**
     * @param $content
     * @return \XF\Phrase
     */
    public function getUnbookmarkPhrase($content)
    {
        return \XF::phrase('th_unbookmark_post_bookmarks');
    }
}