<?php

namespace ThemeHouse\Bookmarks\Pub\Controller;

use XF\Entity\User;
use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

/**
 * Class Bookmark
 * @package ThemeHouse\Bookmarks\Pub\Controller
 */
class Bookmark extends AbstractController
{
    /**
     * @return \XF\Mvc\Reply\View
     * @throws \Exception
     */
    public function actionIndex()
    {
        $bookmarkRepo = $this->getBookmarkRepository();
        $visitor = \XF::visitor();


        $page = $this->filterPage();
        $perPage = $this->options()->th_bookmarksPerPage_bookmarks;

        $contentTypes = $bookmarkRepo->getContentTypeOptions(true);
        $contentType = $this->filter('content_type', 'array', array_keys($contentTypes));

        if (empty($contentType)) {
            $contentType = $this->filter('content_type', 'string');
            $contentType = explode(',', $contentType);
        }

        $bookmarkFinder = $bookmarkRepo->findBookmarksForUser($visitor)->forContentType($contentType);
        $bookmarks = $bookmarkFinder->limitByPage($page, $perPage)->fetch();

        $bookmarkRepo->addContentToBookmarks($bookmarks);
        $bookmarks = $bookmarks->filterViewable();

        $pageNavParams = [
            'content_type' => implode(',', $contentType),
        ];

        $viewParams = [
            'bookmarks' => $bookmarks,
            'contentType' => $contentType,
            'contentTypes' => $contentTypes,

            'page' => $page,
            'perPage' => $perPage,
            'totalBookmarks' => $bookmarkFinder->total(),

            'pageNavParams' => $pageNavParams,
        ];

        return $this->view('ThemeHouse\Bookmarks:Bookmark\Index', 'th_bookmark_list_bookmarks', $viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\View
     * @throws \Exception
     */
    public function actionPopup()
    {
        $options = \XF::options();
        $visitor = \XF::visitor();

        if (!$visitor->user_id) {
            return $this->noPermission();
        }

        $bookmarkRepo = $this->getBookmarkRepository();

        $limit = $options->th_popupLimit_bookmarks;

        $bookmarks = $bookmarkRepo->getBookmarksForPopup()->fetch($limit);
        $bookmarkRepo->addContentToBookmarks($bookmarks);
        $bookmarks = $bookmarks->filterViewable();

        if ($options->th_navigationDropdown_bookmarks === 'disabled') {
            return $this->noPermission();
        }

        $viewParams = [
            'bookmarks' => $bookmarks,
        ];
        return $this->view('ThemeHouse\Bookmarks:Bookmark\Popup', 'th_bookmark_popup_bookmarks', $viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\View
     * @throws \Exception
     */
    public function actionConfirm()
    {
        $contentType = $this->filter('content_type', 'string');
        $contentId = $this->filter('content_id', 'uint');

        /** @var \ThemeHouse\Bookmarks\Entity\Bookmark $bookmark */
        $bookmark = $this->em()->create('ThemeHouse\Bookmarks:Bookmark');

        $bookmark->content_type = $contentType;
        $bookmark->content_id = $contentId;

        if($this->app->options()->thbookmarks_fastBookmark) {
            $existingBookmark = \XF::finder('ThemeHouse\Bookmarks:Bookmark')
                ->where('content_id', '=', $contentId)
                ->where('content_type', '=', $contentType)
                ->where('user_id', '=', \XF::visitor()->user_id)
                ->fetchOne();

            if($existingBookmark) {
                return $this->actionDelete(new ParameterBag(['bookmark_id' => $existingBookmark->bookmark_id]));
            }
            else {
                return $this->actionSave(new ParameterBag());
            }
        }

        return $this->addEditBookmark($bookmark);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     * @throws \Exception
     */
    public function actionEdit(ParameterBag $params)
    {
        $bookmark = $this->assertBookmarkExistsForUser($params['bookmark_id']);

        return $this->addEditBookmark($bookmark);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     * @throws \Exception
     */
    public function actionSave(ParameterBag $params)
    {
        $visitor = \XF::visitor();

        if ($params['bookmark_id']) {
            $bookmark = $this->assertBookmarkExistsForUser($params['bookmark_id']);
        } else {
            $contentId = $this->filter('content_id', 'uint');
            $contentType = $this->filter('content_type', 'string');
            /** @var \ThemeHouse\Bookmarks\Entity\Bookmark $bookmark */
            $bookmark = $this->em()->create('ThemeHouse\Bookmarks:Bookmark');

            $bookmark->content_type = $contentType;
            $bookmark->content_id = $contentId;
            $bookmark->user_id = $visitor->user_id;
        }

        $content = $bookmark->Content;

        if(!$content) {
            if(!$bookmark->isInsert()) {
                $bookmark->delete();
            }

            return $this->notFound();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (!$content->canBookmark() && empty($content->Bookmark)) {
            return $this->noPermission();
        }

        /** @var \ThemeHouse\Bookmarks\Repository\Bookmark $bookmarkRepo */
        $bookmarkRepo = $this->repository('ThemeHouse\Bookmarks:Bookmark');

        /** @var \ThemeHouse\Bookmarks\BookmarkHandler\AbstractHandler $handler */
        $handler = $bookmarkRepo->getHandlerForContentType($bookmark->content_type);
        if (!$handler) {
            return $this->error(\XF::phrase('th_this_content_cannot_be_bookmarked_bookmarks'));
        }

        $this->bookmarkSaveProcess($bookmark)->run();

        $redirect = $this->getDynamicRedirect($handler->getContentUrl($content));

//        $redirect = $this->filter('redirect', 'string');
//        if (!$redirect) {
//            $redirect = $handler->getContentUrl($content);
//        }

        $redirect = $this->redirect($redirect);
        $redirect->setJsonParam('switchKey', 'bookmark');
        return $redirect;
    }

    /**
     * @param \ThemeHouse\Bookmarks\Entity\Bookmark $bookmark
     * @return \XF\Mvc\FormAction
     */
    protected function bookmarkSaveProcess(\ThemeHouse\Bookmarks\Entity\Bookmark $bookmark)
    {
        $input = $this->filter([
            'note' => 'str',
            'sticky' => 'bool',
            'public' => 'bool',
        ]);

        $form = $this->formAction();
        $form->basicEntitySave($bookmark, $input);

        return $form;
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionDelete(ParameterBag $params)
    {
        $bookmark = $this->assertBookmarkExistsForUser($params['bookmark_id']);
        $content = $bookmark->Content;

        if (!$content) {
            $bookmark->delete();
            return $this->notFound();
        }

        $handler = $bookmark->getHandler();

        if ($this->isPost()) {
            $handler->preDeleteBookmark($bookmark, $error);

            if ($error) {
                return $this->error($error);
            }

            $bookmark->delete();

            $handler->postDeleteBookmark($bookmark);

            $redirect = $this->redirect($this->getDynamicRedirect($this->buildLink('bookmarks')));
            $redirect->setJsonParam('switchKey', 'unbookmark');
            return $redirect;
        }

        $viewParams = [
            'bookmark' => $bookmark,

            'handler' => $handler,
            'content' => $bookmark->Content,
        ];

        $view = $this->view('ThemeHouse\Bookmarks:Bookmark\Delete', 'th_bookmark_delete_bookmarks', $viewParams);
        $view->setJsonParam('switchKey', 'unbookmark');
        return $view;
    }

    /**
     * @param \ThemeHouse\Bookmarks\Entity\Bookmark $bookmark
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\View
     * @throws \Exception
     */
    protected function addEditBookmark(\ThemeHouse\Bookmarks\Entity\Bookmark $bookmark)
    {
        /** @var \ThemeHouse\Bookmarks\Repository\Bookmark $bookmarkRepo */
        $bookmarkRepo = $this->repository('ThemeHouse\Bookmarks:Bookmark');

        /** @var \ThemeHouse\Bookmarks\BookmarkHandler\AbstractHandler $handler */
        $handler = $bookmarkRepo->getHandlerForContentType($bookmark->content_type);
        if (!$handler) {
            return $this->error(\XF::phrase('th_this_content_cannot_be_bookmarked_bookmarks'));
        }

        $content = $handler->getContent($bookmark->content_id);
        if (!$content) {
            return $this->notFound();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (!$content->canBookmark() && empty($content->Bookmark)) {
            return $this->noPermission();
        }

        $viewParams = [
            'bookmark' => $bookmark,

            'handler' => $bookmark->getHandler(),
            'content' => $bookmark->Content,

            'contentType' => $bookmark->content_type,
            'contentId' => $bookmark->content_id,

            'redirect' => $this->filter('_xfRedirect', 'string'),
        ];

        return $this->view('ThemeHouse\Bookmarks:Bookmark\Create', 'th_bookmark_edit_bookmarks', $viewParams);
    }

    /**
     * Sorts font list.
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     */
    public function actionSort()
    {
        $bookmarks = $this->getBookmarkRepository()->findBookmarksForUser(\XF::visitor())->fetch();

        if(!$bookmarks->count()) {
            return $this->noPermission(\XF::phrase('thbookmarks_you_have_no_bookmarks_to_sort'));
        }

        if ($this->isPost()) {
            $lastOrder = 0;
            foreach (json_decode($this->filter('bookmarks', 'string')) as $bookmarkValue) {
                $lastOrder += 10;

                /** @var \ThemeHouse\Bookmarks\Entity\Bookmark $bookmark */
                $bookmark = $bookmarks[$bookmarkValue->id];
                $bookmark->display_order = $lastOrder;
                $bookmark->saveIfChanged();
            }

            return $this->redirect($this->buildLink('bookmarks'));
        } else {
            $viewParams = [
                'bookmarks' => $bookmarks
            ];
            return $this->view('ThemeHouse\Bookmarks:Bookmark\Sort', 'thbookmarks_sort_bookmarks', $viewParams);
        }
    }

    /**
     * @param $id
     * @param User $user
     *
     * @return \ThemeHouse\Bookmarks\Entity\Bookmark
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertBookmarkExistsForUser($id, User $user = null)
    {
        if (!$user) {
            $user = \XF::visitor();
        }

        $bookmark = $this->assertBookmarkExists($id);

        if ($bookmark->user_id !== $user->user_id) {
            throw $this->exception(
                $this->notFound(\XF::phrase('requested_page_not_found'))
            );
        }

        return $bookmark;
    }

    /**
     * @param $id
     * @return \ThemeHouse\Bookmarks\Entity\Bookmark
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertBookmarkExists($id)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->assertRecordExists('ThemeHouse\Bookmarks:Bookmark', $id);
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