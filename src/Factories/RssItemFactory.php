<?php declare(strict_types=1);

namespace VitesseCms\Export\Factories;

use VitesseCms\Core\Services\UrlService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Sef\Utils\UtmUtil;
use VitesseCms\Setting\Services\SettingService;
use Suin\RSSWriter\ChannelInterface;
use Suin\RSSWriter\Item;
use Suin\RSSWriter\ItemInterface;

class RssItemFactory
{
    public static function create(
        AbstractCollection $item,
        ChannelInterface $channel,
        string $channelTitle,
        SettingService $setting,
        UrlService $url
    ): ItemInterface
    {
        UtmUtil::setSource('rss_feed');
        UtmUtil::setMedium($channelTitle);
        UtmUtil::setCampaign($item->_('name'));
        $link = UtmUtil::appendToUrl($url->getBaseUri() . $item->_('slug'));

        return (new Item())
            ->title($item->_('name'))
            ->description($item->_('bodytext'))
            ->contentEncoded($item->_('bodytext'))
            ->url($link)// utemen
            ->author($setting->get('WEBSITE_DEFAULT_NAME'))
            ->pubDate(strtotime($item->_('createdAt')))
            ->guid($url->getBaseUri() . $item->_('slug'), true)
            ->preferCdata(true)// By this, title and description become CDATA wrapped HTML.
            ->appendTo($channel);
    }
}
