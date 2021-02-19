<?php declare(strict_types=1);

namespace VitesseCms\Export\Factories;

use Suin\RSSWriter\Feed;
use Suin\RSSWriter\FeedInterface;

class RssFeedFactory
{
    public static function create(): FeedInterface
    {
        return (new Feed());
    }
}
