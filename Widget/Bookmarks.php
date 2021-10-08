<?php

namespace ThemeHouse\Bookmarks\Widget;

use XF\App;
use XF\Http\Request;
use XF\Widget\AbstractWidget;
use XF\Widget\WidgetConfig;

class Bookmarks extends AbstractWidget
{
    protected $defaultOptions = [
        'limit' => 10,
        'limit_content_types' => false,
        'content_types' => [],
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

        $limit = $options['limit'];

        $enabledContentTypes = array_keys($this->_getBookmarkRepo()->getContentTypeOptions(true));

        if ($options['limit_content_types']) {
            $contentTypes = $options['content_types'];

            foreach ($contentTypes as $key=>$contentType) {
                if (!in_array($contentType, $enabledContentTypes)) {
                    unset($contentTypes[$key]);
                }
            }
        } else {
            $contentTypes = $enabledContentTypes;
        }

        $bookmarkFinder = $bookmarkRepo->findBookmarksForUser($visitor)->forContentType($contentTypes);
        if ($options['sticky_only']) {
            $bookmarkFinder->sticky();
        } else {
            $bookmarkFinder->recent();
        }
        $bookmarks = $bookmarkFinder->fetch($limit);

        $bookmarkRepo->addContentToBookmarks($bookmarks);
        $bookmarks = $bookmarks->filterViewable();

        $viewParams = [
            'bookmarks' => $bookmarks,
        ];

        return $this->renderer('th_widget_bookmarks_bookmarks', $viewParams);
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
            'limit' => 'uint',
            'limit_content_types' => 'bool',
            'content_types' => 'array-string',
            'sticky_only' => 'bool',
            'hide_container' => 'bool',
            'hide_note' => 'bool',
            'hide_date' => 'bool',
        ]);

        if ($options['limit'] < 1) {
            $error = \XF::phrase('thbookmarks_bookmark_widget_must_be_configured_to_display_one_or_more_bookmarks');
            return false;
        }

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