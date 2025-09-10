<?php
return [
  'timezone' => 'Europe/Oslo',
  'widgets' => [
    ['name' => 'search', 'id'=>'search', 'x'=>0,  'y'=>0,  'w'=>24, 'h'=>5],
    ['name' => 'rss',    'id'=>'news_general', 'title' => '🗞 General News', 'ttl' => 7200, 'show' => 6, 'pool' => 60, 'x'=>0,  'y'=>5,  'w'=>16, 'h'=>14, 'feeds' => [
      'https://feeds.bbci.co.uk/news/rss.xml',
      'https://www.reuters.com/rssFeed/worldNews'
    ]],
    ['name' => 'weather', 'id'=>'weather', 'ttl' => 3600, 'lat' => 59.9139, 'lon' => 10.7522, 'x'=>16, 'y'=>5,  'w'=>8,  'h'=>10],
    ['name' => 'rss',    'id'=>'news_tech', 'title' => '💻 Tech News', 'ttl' => 7200, 'show' => 6, 'pool' => 60, 'x'=>0,  'y'=>19, 'w'=>16, 'h'=>14, 'feeds' => [
      'https://feeds.arstechnica.com/arstechnica/index/',
      'https://www.theverge.com/rss/index.xml',
      'https://hnrss.org/frontpage'
    ]],
    ['name' => 'quote',  'id'=>'quote', 'ttl' => 21600, 'pool_size' => 25, 'x'=>16, 'y'=>15, 'w'=>8,  'h'=>8],
    ['name' => 'rss',    'id'=>'news_no', 'title' => '🇳🇴 Norge (NRK)', 'ttl' => 7200, 'show' => 6, 'pool' => 60, 'x'=>0,  'y'=>33, 'w'=>16, 'h'=>14, 'feeds' => [
      'https://www.nrk.no/toppsaker.rss',
      'https://www.nrk.no/nyheter/siste.rss'
    ]],
    ['name' => 'reddit', 'id'=>'reddit', 'title' => 'Reddit · last hour', 'ttl' => 1800, 'show' => 10, 'pool' => 60, 'x'=>0,  'y'=>47, 'w'=>24, 'h'=>16, 'subs' => [
      'technology', 'worldnews', 'norway'
    ]],
  ],
];
