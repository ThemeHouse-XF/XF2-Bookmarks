<?php

namespace ThemeHouse\Bookmarks\XF\SubContainer;

/**
 * Class Import
 * @package ThemeHouse\Bookmarks\XF\SubContainer
 */
class Import extends XFCP_Import
{
    public function initialize()
    {
        $initialize = parent::initialize();

        $importers = $this->container('importers');

        $this->container['importers'] = function() use ($importers) {
            $importers[] = 'ThemeHouse\Bookmarks:XonBookmarks17x';
            $importers[] = 'ThemeHouse\Bookmarks:XonBookmarks20x';
            $importers[] = 'ThemeHouse\Bookmarks:AndyBookmark';
            return $importers;
        };

        return $initialize;
    }
}