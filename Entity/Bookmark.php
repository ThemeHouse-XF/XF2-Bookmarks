<?php

namespace ThemeHouse\Bookmarks\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class Bookmark
 * @package ThemeHouse\Bookmarks\Entity
 *
 * @property integer bookmark_id
 * @property string note
 * @property boolean sticky
 * @property boolean public
 * @property integer user_id
 * @property integer content_id
 * @property string content_type
 * @property integer bookmark_date
 *
 * @property Entity Content
 */
class Bookmark extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_bookmark';
        $structure->shortName = 'ThemeHouse\Bookmarks:Bookmark';
        $structure->primaryKey = 'bookmark_id';
        $structure->columns = [
            'bookmark_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'note' => ['type' => self::STR, 'default' => ''],
            'sticky' => ['type' => self::BOOL, 'default' => false],
            'public' => ['type' => self::BOOL, 'default' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'content_id' => ['type' => self::UINT, 'required' => true],
            'content_type' => ['type' => self::STR, 'maxLength' => 25, 'required' => true],
            'bookmark_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'display_order' => ['type' => self::UINT, 'default' => 10]
        ];
        $structure->getters = [
            'link' => true,
            'title' => true,
            'Content' => true,
        ];
        $structure->relations = [];
        $structure->options = [];

        return $structure;
    }

    public function getLink() {
        $handler = $this->getHandler();
        return $handler->getLink($this->Content);
    }

    public function getTitle() {
        $handler = $this->getHandler();
        return $handler->getTitle($this->Content);
    }

    /**
     * @return bool
     */
    protected function _preSave()
    {
        $handler = $this->getHandler();
        if ($this->isInsert()) {
            $handler->preCreateBookmark($this, $error);
        } else {
            $handler->preUpdateBookmark($this, $error);
        }

        if ($error) {
            $this->error($error);
            return false;
        }

        return true;
    }

    protected function _postSave()
    {
        $handler = $this->getHandler();
        if ($this->isInsert()) {
            $handler->postCreateBookmark($this);
        } else {
            $handler->postUpdateBookmark($this);
        }
    }

    /**
     * @return bool
     */
    protected function _preDelete()
    {
        $this->getHandler()->preDeleteBookmark($this, $error);

        if ($error) {
            $this->error($error);
            return false;
        }

        return true;
    }

    protected function _postDelete()
    {
        $this->getHandler()->postDeleteBookmark($this);
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canView(&$error = null)
    {
        $handler = $this->getHandler();
        $content = $this->Content;

        if ($handler && $content) {
            return $handler->canViewContent($content, $error) && $handler->canViewBookmark($this, $error);;
        }

        return false;
    }

    /**
     * @param string $type
     * @param $options
     * @return bool|mixed
     */
    public function render($type = 'popup', $options)
    {
        if (!is_array($options)) {
            $options = [];
        }

        $handler = $this->getHandler();
        $content = $this->Content;

        if ($handler && $content && $this->canView()) {
            return $handler->renderBookmark($this, $type, $options);
        }

        return false;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function renderIcon($type = 'popup')
    {
        $handler = $this->getHandler();
        $content = $this->Content;

        if ($handler && $content && $this->canView()) {
            return $handler->renderIcon($this, $type);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getContentUrl()
    {
        $handler = $this->getHandler();
        $content = $this->Content;

        if ($handler && $content && $this->canView()) {
            return $handler->getContentUrl($content);
        }

        return false;
    }

    /**
     * @param bool $plural
     * @return bool|string|\XF\Phrase
     */
    public function getContentTypePhraseName($plural = false)
    {
        $handler = $this->getHandler();

        if ($handler) {
            return $handler->getContentTypePhraseName($plural);
        }

        return false;
    }

    /**
     * @param Entity|null $content
     */
    public function setContent(Entity $content = null)
    {
        $this->_getterCache['Content'] = $content;
    }

    /**
     * @return null|Entity
     */
    public function getContent()
    {
        $handler = $this->getHandler();
        return $handler ? $handler->getContent($this->content_id) : null;
    }

    /**
     * @return \ThemeHouse\Bookmarks\BookmarkHandler\AbstractHandler
     */
    public function getHandler()
    {
        return $this->getBookmarkRepo()->getHandlerForContentType($this->content_type);
    }

    /**
     * @return \ThemeHouse\Bookmarks\Repository\Bookmark
     */
    protected function getBookmarkRepo()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository('ThemeHouse\Bookmarks:Bookmark');
    }
}