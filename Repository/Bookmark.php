<?php

namespace ThemeHouse\Bookmarks\Repository;

use XF\Entity\User;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

/**
 * Class Bookmark
 * @package ThemeHouse\Bookmarks\Repository
 */
class Bookmark extends Repository
{
    protected static $handlerCache;

    /**
     * @param $contentType
     * @param $contentId
     */
    public function fastDeleteBookmarksForContent($contentType, $contentId)
    {
        $finder = $this->finder('ThemeHouse\Bookmarks:Bookmark')
            ->where([
                'content_type' => $contentType,
                'content_id' => $contentId
            ]);
        $this->deleteBookmarksInternal($finder);
    }

    /**
     * @param $contentType
     * @param $contentId
     */
    public function fastDeleteBookmarksForUser($contentType, $contentId)
    {
        $finder = $this->finder('ThemeHouse\Bookmarks:Bookmark')
            ->where([
                'content_type' => $contentType,
                'content_id' => $contentId
            ]);
        $this->deleteBookmarksInternal($finder);
    }

    /**
     * @param User $user
     * @return \ThemeHouse\Bookmarks\Finder\Bookmark
     */
    public function findBookmarksForUser(User $user)
    {
        return $this->getFinder()
            ->user($user->user_id);
            #->recent();
    }

    /**
     * @param User|null $user
     * @return \ThemeHouse\Bookmarks\Finder\Bookmark
     */
    public function getBookmarksForPopup(User $user = null)
    {
        if (!$user) {
            $user = \XF::visitor();
        }

        $options = \XF::options();
        $finder = $this->findBookmarksForUser($user);
        $type = $options->th_navigationDropdown_bookmarks;

        if ($type === 'recent') {
            $finder->recent();
        } else {
            $finder->sticky();
        }

        return $finder;
    }

    /**
     * @param $bookmarks
     * @throws \Exception
     */
    public function addContentToBookmarks($bookmarks)
    {
        $contentMap = [];
        foreach ($bookmarks AS $key => $bookmark) {
            $contentType = $bookmark->content_type;
            if (!isset($contentMap[$contentType])) {
                $contentMap[$contentType] = [];
            }
            $contentMap[$contentType][$key] = $bookmark->content_id;
        }

        foreach ($contentMap AS $contentType => $contentIds) {
            $handler = $this->getHandlerForContentType($contentType);
            if (!$handler) {
                continue;
            }
            $data = $handler->getContent($contentIds);
            foreach ($contentIds AS $bookmarkId => $contentId) {
                $content = isset($data[$contentId]) ? $data[$contentId] : null;
                /** @noinspection PhpUndefinedMethodInspection */
                $bookmarks[$bookmarkId]->setContent($content);
            }
        }
    }

    /**
     * @param $contentType
     * @return bool
     * @throws \Exception
     */
    public function getHandlerForContentType($contentType)
    {
        $handlerClass = \XF::app()->getContentTypeFieldValue($contentType, 'th_bookmark_handler_class');
        if (!$handlerClass || !class_exists($handlerClass)) {
            return false;
        }

        $handlerClass = \XF::extendClass($handlerClass);
        return new $handlerClass($this->app());
    }

    /**
     * @return \XF\Like\AbstractHandler[]
     * @throws \Exception
     */
    public function getBookmarkHandlers()
    {
        if (!self::$handlerCache) {
            $handlers = [];

            foreach (\XF::app()->getContentTypeField('th_bookmark_handler_class') AS $contentType => $handlerClass) {
                if (class_exists($handlerClass)) {
                    $handlerClass = \XF::extendClass($handlerClass);
                    $handlers[$contentType] = new $handlerClass($this->app());
                }
            }

            self::$handlerCache = $handlers;
        }

        return self::$handlerCache;
    }

    /**
     * @param bool $enabledOnly
     * @param bool $plural
     * @return array
     * @throws \Exception
     */
    public function getContentTypeOptions($enabledOnly = false, $plural = true)
    {
        $options = \XF::options();
        $enabledContentTypes = $options->th_enableContentTypes_bookmarks;
        $handlers = $this->getBookmarkHandlers();

        $contentTypes = [];
        foreach ($handlers as $contentType => $handler) {
            if ($enabledOnly && !in_array($contentType, $enabledContentTypes)) {
                continue;
            }
            /** @noinspection PhpUndefinedMethodInspection */
            if (!$handler->canUse()) {
                continue;
            }
            $phraseKey = \XF::app()->getContentTypePhraseName($contentType, $plural);
            $contentTypes[$contentType] = \XF::phrase($phraseKey);
        }

        return $contentTypes;
    }

    /**
     * @param Finder $finder
     */
    protected function deleteBookmarksInternal(Finder $finder)
    {
        $results = $finder->fetchColumns('bookmark_id');
        if (!$results) {
            return;
        }

        $delete = [];
        foreach ($results AS $result) {
            if (empty($result['alert_id'])) {
                continue;
            }

            $delete[] = $result['alert_id'];
        }

        if (!empty($delete)) {
            $db = $this->db();
            $db->beginTransaction();

            $db->delete('xf_th_bookmark', 'bookmark_id IN (' . $db->quote($delete) . ')');

            $db->commit();
        }
    }

    /**
     * @return \ThemeHouse\Bookmarks\Finder\Bookmark
     */
    protected function getFinder()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->finder('ThemeHouse\Bookmarks:Bookmark')
            ->setDefaultOrder([
                ['display_order', 'ASC'],
                ['bookmark_date', 'DESC']
            ]);
    }
}