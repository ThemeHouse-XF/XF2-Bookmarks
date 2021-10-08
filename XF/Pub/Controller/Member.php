<?php

namespace ThemeHouse\Bookmarks\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Class Member
 * @package ThemeHouse\Bookmarks\XF\Pub\Controller
 */
class Member extends XFCP_Member
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionBookmarks(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params->user_id);

        $bookmarkRepo = $this->getBookmarkRepository();

        $page = $params->page;
        $perPage = $this->options()->th_bookmarksPerPage_bookmarks;

        $bookmarkFinder = $bookmarkRepo->findBookmarksForUser($user)->recent()->isPublic();
        $bookmarks = $bookmarkFinder->limitByPage($page, $perPage)->fetch();

        $viewParams = [
            'user' => $user,
            'bookmarks' => $bookmarks,

            'page' => $page,
            'perPage' => $perPage,
            'totalBookmarks' => $bookmarkFinder->total(),

            'hidePagination' => $this->filter('hide_pagination', 'bool'),
        ];

        return $this->view('ThemeHouse\Bookmarks:Member\Bookmarks', 'th_member_bookmarks', $viewParams);
    }

    /**
     * @return \ThemeHouse\Bookmarks\Repository\Bookmark
     */
    protected function getBookmarkRepository()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository('ThemeHouse\Bookmarks:Bookmark');
    }
}