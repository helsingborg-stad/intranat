<?php

namespace Intranet\Helper;

class FragmentCache
{
    public function __construct()
    {
        add_action('save_post', array($this, 'banNewsCache'));
    }

    /**
     * Ban the fragment cache for intranet news
     * @param int $postId The post id being saved
     * @return void
     */
    public function banNewsCache($postId)
    {
        if (!function_exists('get_sites')) {
            return;
        }

        if (wp_is_post_revision($postId)) {
            return;
        }

        if (get_post_type($postId) == 'intranet-news') {
            clean_post_cache($postId);
        }
    }
}
