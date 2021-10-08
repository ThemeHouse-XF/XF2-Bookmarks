<?php

namespace ThemeHouse\Bookmarks\Stats;

use XF\Stats\AbstractHandler;

class Bookmark extends AbstractHandler
{
    public function getStatsTypes()
    {
        $return = [
            'thbookmarks_total' => \XF::phrase('total'),
            'thbookmarks_thread' => \XF::phrase('threads'),
            'thbookmarks_post' => \XF::phrase('posts'),
            'thbookmarks_node' => \XF::phrase('nodes')
        ];

        $addonCache = $this->app->get('addon.cache');

        if (isset($addonCache['XFRM'])) {
            $return['thbookmarks_resourceItem'] = \XF::phrase('xfrm_resources');
        }
        if (isset($addonCache['XFMG'])) {
            $return['thbookmarks_mediaItem'] = \XF::phrase('xfmg_media');
        }

        return $return;
    }

    public function getData($start, $end)
    {
        $return = [
            'thbookmarks_total' => $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_th_bookmark', 'bookmark_date'),
                [$start, $end]
            ),
            'thbookmarks_thread' => $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_th_bookmark', 'bookmark_date', 'content_type = ?'),
                [$start, $end, 'thread']
            ),
            'thbookmarks_post' => $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_th_bookmark', 'bookmark_date', 'content_type = ?'),
                [$start, $end, 'post']
            ),
            'thbookmarks_node' => $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_th_bookmark', 'bookmark_date', 'content_type = ?'),
                [$start, $end, 'node']
            )
        ];

        $addonCache = $this->app->get('addon.cache');

        if (isset($addonCache['XFRM'])) {
            $return['thbookmarks_resourceItem'] = $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_th_bookmark', 'bookmark_date', 'content_type = ?'),
                [$start, $end, 'resource']
            );
        }
        if (isset($addonCache['XFMG'])) {
            $return['thbookmarks_mediaItem'] = $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_th_bookmark', 'bookmark_date', 'content_type = ?'),
                [$start, $end, 'xfmg_media']
            );
        }

        return $return;
    }
}