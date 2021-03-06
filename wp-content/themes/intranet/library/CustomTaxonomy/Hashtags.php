<?php

namespace Intranet\CustomTaxonomy;

class Hashtags
{
    public static $taxonomySlug = 'hashtag';
    public static $nameSingular = 'Hashtag';
    public static $namePlural = 'Hashtags';

    public function __construct()
    {
        add_action('init', array($this, 'registerHashtags'), 11);
        add_action('save_post', array($this, 'savePostHashtags'), 10, 3);
        add_action('wp_insert_comment', array($this, 'saveCreatedCommentHashtags'), 99, 2);

        add_filter('wp_update_comment_data', array($this, 'saveUpdatedCommentHashtags'), 10, 3);
        add_filter('the_content', array($this, 'hashtagReplace'), 200, 1);
        add_filter('comment_text', array($this, 'hashtagReplace'), 20, 1);
        add_filter('Municipio/archive/filter_taxonomies', array($this, 'filterTaxonomies'), 10, 2);
        add_filter('Municipio/archive/tax_query', array($this, 'taxQuery'), 10, 2);
        add_filter('pre_insert_term', array($this, 'stripTermHashtags'), 10, 2);
        add_action('pre_get_posts', array($this, 'doPostTaxonomyFiltering'));
        add_action('wp_ajax_get_hashtags', array($this, 'getHashtags'));
        add_action('pre_get_posts', array($this, 'taxonomyArchiveQuery'), 99);
    }

    /**
     * Register Hashtag taxonomy
     * @return void
     */
    public function registerHashtags()
    {
        $labels = array(
            'name'              => self::$namePlural,
            'singular_name'     => self::$nameSingular,
            'search_items'      => sprintf(__('Search %s', 'municipio-intranet'), self::$namePlural),
            'all_items'         => sprintf(__('All %s', 'municipio-intranet'), self::$namePlural),
            'parent_item'       => sprintf(__('Parent %s:', 'municipio-intranet'), self::$nameSingular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'municipio-intranet'), self::$nameSingular) . ':',
            'edit_item'         => sprintf(__('Edit %s', 'municipio-intranet'), self::$nameSingular),
            'update_item'       => sprintf(__('Update %s', 'municipio-intranet'), self::$nameSingular),
            'add_new_item'      => sprintf(__('Add New %s', 'municipio-intranet'), self::$nameSingular),
            'new_item_name'     => sprintf(__('New %s Name', 'municipio-intranet'), self::$nameSingular),
            'menu_name'         => self::$namePlural,
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => false,
            'hierarchical'          => false,
            'show_tagcloud'         => false,
            'show_ui'               => true,
            'query_var'             => true,
            'show_in_rest'          => true,
            'rewrite'               => array('with_front' => false, 'slug' => self::$taxonomySlug),
        );

        $postTypes = get_post_types(array('public' => true));

        register_taxonomy(self::$taxonomySlug, $postTypes, $args);
    }

    /**
     * Save hashtags as taxonomies when a comment is created
     * @param  int $commentId  Comment ID
     * @param  obj $commentObj Comment object
     * @return void
     */
    public function saveCreatedCommentHashtags($commentId, $commentObj)
    {
        if (!is_user_logged_in()) {
            return;
        }

        $hashtags = $this->extractHashtags($commentObj->comment_content);
        if ($hashtags) {
            wp_set_object_terms($commentObj->comment_post_ID, $hashtags, self::$taxonomySlug, true);
        }
    }

    /**
     * Save hashtags as taxonomies when a comment is updated
     * @param array $data       The new, processed comment data.
     * @param array $comment    The old, unslashed comment data.
     * @param array $commentArr The new, raw comment data.
     */
    public function saveUpdatedCommentHashtags($data, $comment, $commentArr)
    {
        if (!is_user_logged_in()) {
            return $data;
        }

        $hashtags = $this->extractHashtags($data['comment_content']);
        if ($hashtags) {
            wp_set_object_terms($data['comment_post_ID'], $hashtags, self::$taxonomySlug, true);
        }

        return $data;
    }

    /**
     * Save hashtags as taxonomies from the content
     * @param  int  $postId The post ID
     * @param  obj  $post   The post object
     * @param bool  $update Whether this is an existing post being updated or not
     * @return void
     */
    public function savePostHashtags($postId, $post, $update)
    {
        // Save hashtags from the content
        $hashtags = $this->extractHashtags($post->post_content);
        if ($hashtags) {
            wp_set_object_terms($postId, $hashtags, self::$taxonomySlug, true);
        }
    }

    /**
     * Include post children in tax archive query
     * @param  object $query Query object
     * @return object        Modified query
     */
    public function taxonomyArchiveQuery($query)
    {
        // Only execute in taxonomy archive pages
        if (is_admin() || !(is_tax() || is_tag()) ||!$query->is_main_query()) {
            return $query;
        }

        $query->set('post_parent', '');
        return $query;
    }

    /**
     * Get list of all Hashtags
     */
    public function getHashtags()
    {
        if (!$hashtags = wp_cache_get('tinymce_hashtags')) {
            $terms = get_terms([
                'taxonomy' => self::$taxonomySlug,
                'hide_empty' => false,
            ]);

            $hashtags = array();
            if (!empty($terms)) {
                foreach ($terms as $key => $term) {
                    $hashtags[] = array('display_name' => $term->name);
                }
            }

            wp_cache_add('tinymce_hashtags', $hashtags);
        }

        wp_send_json($hashtags);
    }

    public function taxQuery($taxQuery, $query)
    {
        $hashtags = isset($query->query['s']) ? $this->extractHashtags($query->query['s']) : false;

        if (!$hashtags) {
            return $taxQuery;
        }

        $taxQuery[] =
                array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'hashtag',
                        'terms' => $hashtags,
                        'field' => 'name',
                        'operator' => 'IN',
                        'include_children' => false,
                    )
                );

        return $taxQuery;
    }

    /**
     * Do taxonomy fitering
     * @param  object $query Query object
     * @return object        Modified query
     */
    public function doPostTaxonomyFiltering($query)
    {
        // Do not execute this in admin view
        if (is_admin() || !(is_archive() || is_home() || is_category() || is_tax() || is_tag()) || !$query->is_main_query()) {
            return $query;
        }

        $hashtags = isset($query->query['s']) ? $this->extractHashtags($query->query['s']) : false;
        if ($hashtags) {
            $query->set('s', '');
        }

        return $query;
    }

    /**
     * Make hashtags searchable on all post type archives
     * @param  array  $taxonomies Filterable taxonomies for current post type
     * @param  string $postType   Current post type
     * @return array              Modified list of taxonomies
     */
    public function filterTaxonomies($taxonomies, $postType)
    {
        if (!array_key_exists(self::$taxonomySlug, $taxonomies)) {
            $taxonomies[self::$taxonomySlug] = self::$taxonomySlug;
        }

        return $taxonomies;
    }

    /**
     * Replaces hashtags in text with clickable links
     * @param  string $content Post content
     * @return string          Modified post content
     */
    public function hashtagReplace($content)
    {
        // Match #hashtags and replace with url, skip if hashtag is inside textarea
        $content = preg_replace_callback('/(<[textarea|a][^>]*>.*?<\/[textarea|a]>|<[input][^>]*\/>)(*SKIP)(*FAIL)|(?<!=|[\w\/\"\'\\\]|&)#([\w]+)/ius',
            function ($match) {
                $hashtag = $match[0];

                // Get taxonomy object
                $term = get_term_by('name', $match[2], 'hashtag');
                if (!empty($term->count)) {
                    $url = get_term_link($term);
                    $hashtag = '<a href="' . $url . '">' . $match[0] . '</a>';
                }

                return $hashtag;
        }, $content);

        return $content;
    }

    /**
     * Returns hashtags from string
     * @param  string $string String to check for hashtags
     * @return mixed          Array if string contains hashtags
     */
    public function extractHashtags($string)
    {
        $hashtags = false;
        preg_match_all('/<[^>]*>.*?<\/[^>]*>(*SKIP)(*FAIL)|(?<!=|[\w\/\"\'\\\]|&)#([\w]+)/ius', $string, $matches);
        if ($matches) {
            $hashtagsArray = array_count_values($matches[1]);
            $hashtags = array_keys($hashtagsArray);
        }

        return $hashtags;
    }

    /**
     * Removes '#' from hashtag term
     * @param  string $term Term to be saved
     * @param  string $tax  Taxonomy
     * @return string       Modified term
     */
    public function stripTermHashtags($term, $tax)
    {
        if ($tax == self::$taxonomySlug) {
            $term = str_replace('#', '', $term);
        }

        return $term;
    }
}
