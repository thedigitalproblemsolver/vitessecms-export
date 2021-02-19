<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Export\Factories\RssChannelFactory;
use VitesseCms\Export\Factories\RssFeedFactory;
use VitesseCms\Export\Factories\RssItemFactory;

class RssExportHelper extends AbstractExportHelper
{
    public function createOutput(): string
    {
        $channelTitle = $this->exportType->getNameField() . ' - ' . $this->setting->get('WEBSITE_DEFAULT_NAME');
        $feed = RssFeedFactory::create();
        $channel = RssChannelFactory::create(
            $feed,
            $channelTitle,
            $this->setting
        );

        /** @var AbstractCollection $item */
        foreach ($this->items as $items) :
            foreach ($items as $item) :
                RssItemFactory::create($item, $channel, $channelTitle, $this->setting, $this->url);
            endforeach;
        endforeach;

        return $feed->render();
    }
}
