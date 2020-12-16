<?php

namespace VitesseCms\Export\Factories;

use Suin\RSSWriter\Feed;
use Suin\RSSWriter\FeedInterface;

/**
 * Class RssFeedFactory
 */
class RssFeedFactory
{
    /**
     * @return FeedInterface
     */
    public static function create(): FeedInterface
    {
        return (new Feed());
    }
}
