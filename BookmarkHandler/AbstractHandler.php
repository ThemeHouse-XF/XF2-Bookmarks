<?php

namespace ThemeHouse\Bookmarks\BookmarkHandler;

use ThemeHouse\Bookmarks\Entity\Bookmark;
use XF\App;
use XF\Mvc\Entity\Entity;
use XF\Template\Templater;

/**
 * Class AbstractHandler
 * @package ThemeHouse\Bookmarks\BookmarkHandler
 */
abstract class AbstractHandler
{
    protected $contentType;

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param $id
     * @return null|\XF\Mvc\Entity\ArrayCollection|Entity
     */
    public function getContent($id)
    {
        return $this->app->findByContentType($this->contentType, $id, $this->getEntityWith());
    }

    /**
     * @param bool $plural
     * @param bool $render
     * @return string|\XF\Phrase
     */
    public function getContentTypePhraseName($plural = false, $render = true)
    {
        $phraseKey = \XF::app()->getContentTypePhraseName($this->contentType, $plural);

        if ($render) {
            return \XF::phrase($phraseKey);
        }

        return $phraseKey;
    }

    /**
     * @param Entity $entity
     * @param null $error
     * @return mixed
     */
    public function canViewContent(Entity $entity, &$error = null)
    {
        if (method_exists($entity, 'canView'))
        {
            return $entity->canView($error);
        }

        throw new \LogicException("Could not determine content viewability; please override");
    }

    /**
     * @param Bookmark $bookmark
     * @param null $error
     * @return bool
     */
    public function canViewBookmark(Bookmark $bookmark, &$error = null)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canUse()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param Bookmark $bookmark
     * @param $type
     * @return bool
     */
    public function renderIcon(Bookmark $bookmark, $type)
    {
        return $this->_renderIcon($bookmark, $type, $this->app->templater());
    }

    /**
     * @param Bookmark $bookmark
     * @param $type
     * @param array $options
     * @return mixed
     */
    public function renderBookmark(Bookmark $bookmark, $type, Array $options = [])
    {
        return $this->_renderBookmark($bookmark, $type, $options, $this->app->templater());
    }

    /**
     * @param $content
     * @return bool
     */
    public function renderExtraCreateFields($content)
    {
        return $this->_renderExtraCreateFields($content, $this->app->templater());
    }

    /**
     * @param $content
     * @return bool
     */
    public function renderExtraEditFields($content)
    {
        return $this->_renderExtraEditFields($content, $this->app->templater());
    }

    /**
     * @param $content
     * @return bool
     */
    public function renderExtraDeleteFields($content)
    {
        return $this->_renderExtraDeleteFields($content, $this->app->templater());
    }

    /**
     * @param Bookmark $bookmark
     * @param bool $error
     * @return bool
     */
    public function preCreateBookmark(Bookmark $bookmark, &$error = false)
    {
        return $this->_preCreateBookmark($bookmark, $error);
    }

    /**
     * @param Bookmark $bookmark
     * @return bool
     */
    public function postCreateBookmark(Bookmark $bookmark)
    {
        return $this->_postCreateBookmark($bookmark);
    }

    /**
     * @param Bookmark $bookmark
     * @param bool $error
     * @return bool
     */
    public function preUpdateBookmark(Bookmark $bookmark, &$error = false)
    {
        return $this->_preUpdateBookmark($bookmark, $error);
    }

    /**
     * @param Bookmark $bookmark
     * @return bool
     */
    public function postUpdateBookmark(Bookmark $bookmark)
    {
        return $this->_postUpdateBookmark($bookmark);
    }

    /**
     * @param Bookmark $bookmark
     * @param bool $error
     * @return bool
     */
    public function preDeleteBookmark(Bookmark $bookmark, &$error = false)
    {
        return $this->_preDeleteBookmark($bookmark, $error);
    }

    /**
     * @param Bookmark $bookmark
     * @return bool
     */
    public function postDeleteBookmark(Bookmark $bookmark)
    {
        return $this->_postDeleteBookmark($bookmark);
    }

    /**
     * @return array
     */
    public function getEntityWith()
    {
        return [];
    }

    public abstract function getContentUrl($content);
    public abstract function getBookmarkPhrase($content);
    public abstract function getUnbookmarkPhrase($content);

    public abstract function getTitle($content);
    public abstract function getLink($content);

    /**
     * @param Bookmark $bookmark
     * @param $type
     * @param array $options
     * @param Templater $templater
     * @return mixed
     */
    protected abstract function _renderBookmark(Bookmark $bookmark, $type, Array $options = [], Templater $templater);

    /**
     * @param Bookmark $bookmark
     * @param $type
     * @param Templater $templater
     * @return bool
     */
    protected function _renderIcon(Bookmark $bookmark, $type, Templater $templater)
    {
        return false;
    }

    /**
     * @param Bookmark $bookmark
     * @param $error
     * @return bool
     */
    protected function _preDeleteBookmark(Bookmark $bookmark, &$error)
    {
        return true;
    }

    /**
     * @param Bookmark $bookmark
     * @return bool
     */
    protected function _postDeleteBookmark(Bookmark $bookmark)
    {
        return true;
    }

    /**
     * @param Bookmark $bookmark
     * @param $error
     * @return bool
     */
    protected function _preCreateBookmark(Bookmark $bookmark, &$error)
    {
        return true;
    }

    /**
     * @param Bookmark $bookmark
     * @return bool
     */
    protected function _postCreateBookmark(Bookmark $bookmark)
    {
        return true;
    }

    /**
     * @param Bookmark $bookmark
     * @param $error
     * @return bool
     */
    protected function _preUpdateBookmark(Bookmark $bookmark, &$error)
    {
        return true;
    }

    /**
     * @param Bookmark $bookmark
     * @return bool
     */
    protected function _postUpdateBookmark(Bookmark $bookmark)
    {
        return true;
    }

    /**
     * @param $content
     * @param Templater $templater
     * @return bool
     */
    protected function _renderExtraCreateFields($content, Templater $templater)
    {
        return false;
    }

    /**
     * @param $content
     * @param Templater $templater
     * @return bool
     */
    protected function _renderExtraEditFields($content, Templater $templater)
    {
        return false;
    }

    /**
     * @param $content
     * @param Templater $templater
     * @return bool
     */
    protected function _renderExtraDeleteFields($content, Templater $templater)
    {
        return false;
    }

    /**
     * @return \XF\Mvc\Entity\Manager
     */
    public function em()
    {
        return $this->app->em();
    }

    /**
     * @param string $type
     *
     * @return \XF\Mvc\Entity\Finder
     */
    public function finder($type)
    {
        return $this->app->em()->getFinder($type);
    }

    /**
     * @param string $identifier
     *
     * @return \XF\Mvc\Entity\Repository
     */
    public function repository($identifier)
    {
        return $this->app->em()->getRepository($identifier);
    }

    /**
     * @param string $class
     *
     * @return \XF\Service\AbstractService
     */
    public function service($class)
    {
        return call_user_func_array([$this->app, 'service'], func_get_args());
    }
}