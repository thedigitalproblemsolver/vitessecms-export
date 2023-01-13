<?php declare(strict_types=1);

namespace VitesseCms\Export\Factories;

use VitesseCms\Setting\Services\SettingService;
use Phalcon\Di\Di;
use Suin\RSSWriter\Channel;
use DateTime;
use Suin\RSSWriter\ChannelInterface;
use Suin\RSSWriter\FeedInterface;

class RssChannelFactory
{
    public static function create(
        FeedInterface  $feed,
        string         $channelTitle,
        SettingService $setting,
        string         $channelDescription = ''
    ): ChannelInterface
    {
        $di = Di::getDefault();
        $dateTime = new DateTime();

        return (new Channel())
            ->title($channelTitle)
            ->description($channelDescription)
            ->url($di->get('url')->getBaseUri())
            ->feedUrl($di->get('url')->getBaseUri() . '/export/rss/id')
            ->language($di->get('config')->get('language')->get('locale'))
            ->copyright(
                'Copyright ' .
                $dateTime->format('Y') . ', ' .
                $setting->get('WEBSITE_DEFAULT_NAME')
            )
            ->pubDate($dateTime->getTimestamp())
            ->lastBuildDate($dateTime->getTimestamp())
            ->ttl(86400)
            //->pubsubhubbub('http://example.com/feed.xml', 'http://pubsubhubbub.appspot.com') // This is optional. Specify PubSubHubbub discovery if you want.
            ->appendTo($feed);
    }
}
