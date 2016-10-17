<?php
    $news = false;
    $display = get_field('display', $module->ID);
    $limit = !empty(get_field('limit', $module->ID)) ? get_field('limit', $module->ID) : 10;
    $helpTooltip = false;

    switch ($display) {
        default:
        case 'network_subscribed':
            if (is_user_logged_in()) {
                $subscriptions = array_merge(
                    (array) \Intranet\User\Subscription::getForcedSubscriptions(true),
                    (array) \Intranet\User\Subscription::getSubscriptions(null, true)
                );

                $news = \Intranet\CustomPostType\News::getNews($limit, $subscriptions);
                $helpTooltip = true;
            } else {
                $news = \Intranet\CustomPostType\News::getNews($limit, 'all');
            }

            break;

        case 'network':
            $news = \Intranet\CustomPostType\News::getNews($limit, 'all');
            break;

        case 'blog':
            $news = \Intranet\CustomPostType\News::getNews($limit, array(get_current_blog_id()));
            break;
    }

    if (count($news) > 0) :

    $hasImages = false;

    if (get_field('placeholders', $module->ID)) {
        foreach ($news as $item) {
            if (get_thumbnail_source($item->ID) !== false) {
                $hasImages = true;
            }
        }
    }
?>
<div class="grid intranet-news">

    <?php
    if ($helpTooltip) {
        echo municipio_intranet_walkthrough(
            __('News feed', 'municipio-intranet'),
            __('This is your personalized news feed. The personalized news feed will show the news that affects all employees and from the intranets that you are following. You will also see news from the target groups that you might have selected in your profile settings. ', 'municipio-intranet'),
            '.intranet-news',
            'top-center'
        );
    }
    ?>


    <?php if (!$module->hideTitle) : ?>
    <div class="grid-xs-12">
        <h2><?php echo $module->post_title; ?></h2>
    </div>
    <?php endif; ?>

    <?php foreach ($news as $item) : ?>
        <div class="grid-lg-12">
            <a <?php if (is_super_admin()) : ?>title="Rank: <?php echo round($item->rank_percent, 3); ?>%"<?php endif; ?> href="<?php echo get_blog_permalink($item->blog_id, $item->ID); ?>" class="<?php echo implode(' ', apply_filters('Modularity/Module/Classes', array('box', 'box-news', 'box-news-horizontal'), $module->post_type, $args)); ?> <?php echo $item->is_sticky ? 'is-sticky' : ''; ?>">
                <?php if (!in_array($args['id'], array('content-area', 'content-area-top')) && ($hasImages || (!get_field('placeholders', $module->ID) && $item->thumbnail_image))) : ?>
                    <div class="box-image-container">
                        <?php if ($item->thumbnail_image) : ?>
                        <img class="box-image" src="<?php echo $item->thumbnail_image[0]; ?>" alt="<?php echo $item->post_title; ?>">
                        <?php else : ?>
                        <figure class="image-placeholder"></figure>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="box-content">
                    <time class="pricon pricon-clock pricon-space-right" datetime="<?php echo mysql2date('Y-m-d H:i:s', strtotime($item->post_date)); ?>"><?php echo mysql2date(get_option('date_format'), $item->post_date); ?></time>
                    <h3 class="box-title text-highlight"><?php echo apply_filters('the_title', $item->post_title); ?></h3>
                    <div class="sub-heading">
                        <?php echo municipio_intranet_format_site_name(\Intranet\Helper\Multisite::getSite($item->blog_id), 'long'); ?>
                    </div>

                    <article class="clearfix">
                        <?php if (in_array($args['id'], array('content-area', 'content-area-top')) && ($hasImages || (!get_field('placeholders', $module->ID) && $item->thumbnail_image))) : ?>
                            <?php if ($item->thumbnail_image) : ?>
                            <img src="<?php echo $item->thumbnail_image[0]; ?>" alt="<?php echo $item->post_title; ?>">
                            <?php else : ?>
                            <figure class="image-placeholder"></figure>
                            <?php endif; ?>
                        <?php endif; ?>

                        <p><?php echo isset(get_extended($item->post_content)['extended']) && !empty(get_extended($item->post_content)['extended']) ? wp_strip_all_tags(get_extended($item->post_content)['main']) : wp_trim_words($item->post_content, 50, ''); ?></p>
                    </article>
                    <div><span class="read-more inline-block"><?php _e('Read more', 'modularity'); ?></span></div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php else : ?>
<div class="grid">
    <?php if (!empty($module->post_title)) : ?>
    <div class="grid-xs-12">
        <h2><?php echo $module->post_title; ?></h2>
    </div>
    <?php endif; ?>
    <div class="grid-xs-12">
        <?php _e('Threre\'s no news stories to display', 'municipio-intranet'); ?> <i class="pricon pricon-smiley-sad"></i>
    </div>
</div>
<?php endif; ?>

