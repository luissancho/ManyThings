<?php

namespace ManyThings\Controllers;

use ManyThings\Core\Controller;

class SitemapController extends Controller
{
    public function indexAction()
    {
        $countries = SiteService::getCountries();

        $sitemap = new \DOMDocument('1.0', 'UTF-8');

        $index = $sitemap->createElement('sitemapindex');
        $index->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $index->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $url = $sitemap->createElement('url');
        $url->appendChild($sitemap->createElement('loc', DOMPATH . '/'));
        $url->appendChild($sitemap->createElement('changefreq', 'weekly'));
        $url->appendChild($sitemap->createElement('priority', '1.0'));
        $index->appendChild($url);

        $sitemap->appendChild($index);

        $this->response->sendXml($sitemap->saveXML());
    }
}
