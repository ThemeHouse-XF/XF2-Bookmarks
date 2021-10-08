<?php

namespace ThemeHouse\Bookmarks\Import\Importer;

use XF\Import\StepState;

/**
 * Class XonBookmarks17x
 * @package ThemeHouse\Bookmarks\Import\Importer
 */
class XonBookmarks17x extends AbstractBookmarkImporter
{
    /**
     * @return array
     */
    public static function getListInfo()
    {
        return [
            'target' => '[TH] Bookmarks',
            'source' => 'Bookmarks by Xon 1.7.x',
        ];
    }

    /**
     * @return array
     */
    protected function getBaseConfigDefault()
    {
        return [];
    }

    /**
     * @param array $vars
     * @return string
     */
    public function renderBaseConfigOptions(array $vars)
    {
        return $this->app->templater()->renderTemplate('admin:th_import_config_bookmarks_bookmarks', $vars);
    }

    /**
     * @param array $baseConfig
     * @param array $errors
     * @return bool
     */
    public function validateBaseConfig(array &$baseConfig, array &$errors)
    {
        return true;
    }

    /**
     * @return array
     */
    protected function getStepConfigDefault()
    {
        return [];
    }

    /**
     * @param array $vars
     * @return bool
     */
    public function renderStepConfigOptions(array $vars)
    {
        return false;
    }

    /**
     * @param array $steps
     * @param array $stepConfig
     * @param array $errors
     * @return bool
     */
    public function validateStepConfig(array $steps, array &$stepConfig, array &$errors)
    {
        return true;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return [
            'bookmarks' => [
                'title' => 'Bookmarks',
            ],
        ];
    }

    /**
     * @return int
     */
    public function getStepEndBookmarks()
    {
        return $this->db()->fetchOne('SELECT MAX(bookmark_id) FROM bookmark_content') ?: 0;
    }

    /**
     * @param StepState $state
     * @param array $stepConfig
     * @param $maxTime
     * @return $this|StepState
     * @throws \Exception
     */
    public function stepBookmarks(StepState $state, array $stepConfig, $maxTime)
    {
        $limit = 1;

        $bookmarks = $this->db()->fetchAll('
            SELECT *
            FROM bookmark_content
            WHERE bookmark_id > ? AND bookmark_id <= ?
            LIMIT ' . $limit, [
            $state->startAfter,
            $state->end,
        ]);

        if (!$bookmarks) {
            return $state->complete();
        }

        foreach ($bookmarks as $bookmark) {
            $oldId = $bookmark['bookmark_id'];
            $state->startAfter = $oldId;

            $import = $this->setupImportBookmark($bookmark);
            if ($import) {
                $import->save($oldId);
                $state->imported++;
            }
        }

        return $state;
    }

    /**
     * @param array $bookmark
     * @return \XF\Import\Data\AbstractData
     */
    protected function setupImportBookmark(array $bookmark)
    {
        $import = $this->newHandler('ThemeHouse\Bookmarks:Bookmark');

        $data = $this->mapKeys($bookmark, [
            'content_type',
            'content_id',
            'bookmark_user_id' => 'user_id',
            'bookmark_date',
            'bookmark_note' => 'note',
            'public',
            'sticky'
        ]);

        $import->bulkSet($data);
        return $import;
    }

    protected function doInitializeSource()
    {

    }

}