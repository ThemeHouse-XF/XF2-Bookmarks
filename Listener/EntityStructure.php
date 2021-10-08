<?php

namespace ThemeHouse\Bookmarks\Listener;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Manager;
use XF\Mvc\Entity\Structure;

/**
 * Class EntityStructure
 * @package ThemeHouse\Bookmarks\Listener
 */
class EntityStructure
{
    protected static function bookmarkRelation(Manager $em, Structure &$structure)
    {

        $visitor = \XF::visitor();
        $structure->relations['Bookmark'] = [
            'type' => Entity::TO_ONE,
            'entity' => 'ThemeHouse\Bookmarks:Bookmark',
            'conditions' => [
                ['content_type', '=', $structure->contentType],
                ['content_id', '=', '$' . $structure->primaryKey],
                ['user_id', '=', $visitor->user_id],
            ],
        ];
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfUser(Manager $em, Structure &$structure) {
        $structure->relations['Bookmarks'] = [
            'type' => Entity::TO_MANY,
            'entity' => 'ThemeHouse\Bookmarks:Bookmark',
            'conditions' => 'user_id',
            'primary' => true
        ];
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfPost(Manager $em, Structure &$structure)
    {
        self::bookmarkRelation($em, $structure);
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfThread(Manager $em, Structure &$structure)
    {
        self::bookmarkRelation($em, $structure);
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfNode(Manager $em, Structure &$structure)
    {
        $structure->contentType = 'node';

        self::bookmarkRelation($em, $structure);
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfrmResourceItem(Manager $em, Structure &$structure)
    {
        self::bookmarkRelation($em, $structure);
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfmgMediaItem(Manager $em, Structure &$structure)
    {
        self::bookmarkRelation($em, $structure);
    }
}