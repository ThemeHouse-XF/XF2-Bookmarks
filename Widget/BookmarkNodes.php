<?php

namespace ThemeHouse\Bookmarks\Widget;

use XF\App;
use XF\Http\Request;
use XF\Widget\AbstractWidget;
use XF\Widget\WidgetConfig;

class BookmarkNodes extends AbstractWidget
{
    protected $defaultOptions = [
        'limit' => 10,
        'limit_content_types' => false,
        'sticky_only' => false,
        'hide_container' => false,
        'hide_note' => false,
        'hide_date' => false,
    ];

    /**
     * @param $context
     * @return array
     * @throws \Exception
     */
    public function getDefaultTemplateParams($context)
    {
        $params = parent::getDefaultTemplateParams($context);

        if ($context === 'options') {
            $bookmarkRepo = $this->_getBookmarkRepo();
            $params['contentTypes'] = $bookmarkRepo->getContentTypeOptions(true);
        }

        return $params;
    }

    /**
     * @return \XF\Widget\WidgetRenderer
     * @throws \Exception
     */
    public function render()
    {
        $visitor = \XF::visitor();
        $bookmarkRepo = $this->_getBookmarkRepo();
        $options = $this->options;

        $bookmarkFinder = $bookmarkRepo->findBookmarksForUser($visitor)->forContentType(['node']);
        if ($options['sticky_only']) {
            $bookmarkFinder->sticky();
        } else {
            $bookmarkFinder->recent();
        }
        $bookmarks = $bookmarkFinder->fetch();

        $bookmarkRepo->addContentToBookmarks($bookmarks);
        $bookmarks = $bookmarks->filterViewable();

        $viewParams = [
            'bookmarks' => $bookmarks,
        ];

        return $this->renderer('th_widget_bookmarks_bookmarkNodes', $viewParams);
    }

    /**
     * @param Request $request
     * @param array $options
     * @param null $error
     * @return bool
     */
    public function verifyOptions(Request $request, array &$options, &$error = null)
    {
        $options = $request->filter([
            'content_types' => 'array-string',
            'sticky_only' => 'bool',
            'hide_container' => 'bool',
            'hide_note' => 'bool',
            'hide_date' => 'bool',
        ]);

        return true;
    }

    /**
     * @return \ThemeHouse\Bookmarks\Repository\Bookmark
     */
    protected function _getBookmarkRepo()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository('ThemeHouse\Bookmarks:Bookmark');
    }
}
