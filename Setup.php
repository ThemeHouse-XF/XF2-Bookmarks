<?php

namespace ThemeHouse\Bookmarks;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

/**
 * Class Setup
 * @package ThemeHouse\Bookmarks
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->createTable('xf_th_bookmark', function (Create $table) {
            $table->addColumn('bookmark_id', 'int')->autoIncrement();
            $table->addColumn('note', 'varchar', 250);
            $table->addColumn('sticky', 'bool')->setDefault(0);
            $table->addColumn('public', 'bool')->setDefault(1);
            $table->addColumn('user_id', 'int');
            $table->addColumn('content_id', 'int');
            $table->addColumn('content_type', 'varbinary', 25);
            $table->addColumn('bookmark_date', 'int');
            $table->addColumn('display_order', 'int');

            $table->addKey('user_id');
            $table->addKey('content_type');
            $table->addKey(['user_id', 'content_id']);
        });
    }

    public function installStep2()
    {
        $this->applyGlobalPermission('th_bookmarks', 'canBookmark', 'forum', 'like');
        $this->applyGlobalPermissionInt('th_bookmarks', 'maxBookmarks', -1);
    }

    public function upgrade1000297Step1() {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_th_bookmark', function (Alter $table) {
            $table->addColumn('display_order', 'int');
        });

        $this->applyGlobalPermissionInt('th_bookmarks', 'maxBookmarks', -1);
    }

    public function upgrade1000393Step1() {
        $this->schemaManager()->alterTable('xf_th_bookmark', function (Alter $table) {
            $table->addKey('user_id');
            $table->addKey('content_type');
            $table->addKey(['user_id', 'content_id']);
        });
    }

    public function uninstallStep1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->dropTable('xf_th_bookmark');
    }

    /**
     * @param array $newContentTypes
     */
    protected function activateNewContentTypes(Array $newContentTypes = [])
    {
        $options = \XF::options();

        $newValues = $options->th_enableContentTypes_bookmarks;
        foreach ($newContentTypes as $contentType) {
            if (!in_array($contentType, $options->th_enableContentTypes_bookmarks)) {
                $newValues[] = $contentType;
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        \XF::repository('XF:Option')->updateOptions([
            'th_enableContentTypes_bookmarks' => $newValues,
        ]);
    }}