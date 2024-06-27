# Corepulse Cache

## Getting started

Clone source and rename folder to {project}/bundles/CorepulseCacheBundle

## Config

Update file `composer.json` and run `composer dump-autoload`:
```
"autoload": {
    "psr-4": {
      ...
      "CorepulseCacheBundle\\": "bundles/CorepulseCacheBundle/src",
      
    }
  },
```

Update file `config/config.yaml`:
```
pimcore:
    bundles:
        search_paths:
        	...
            - bundles/CorepulseCacheBundle
```

```
framework:
    messenger:
        transports:
            async: "%env(MESSENGER_TRANSPORT_DSN)%" # Xử lý tin không đồng bộ
            sync: 'sync://' # Xử lý tin nhắn đồng bộ
```

Update `.env`:
```
MESSENGER_TRANSPORT_DSN=doctrine://default
COPULSECACHE_TRANSPORT_NAME=sync
COPULSECACHE_CDN=https://khanhtest.b-cdn.net
COPULSECACHE_OLD_DOMAIN=
```

Update file `config/bundles.php`:
```
return [
	...
	CorepulseCacheBundle\CorepulseCacheBundle::class => ['all' => true],
]
```

## Installation

run command:
```
./bin/console pimcore:bundle:install CorepulseCacheBundle
```

## Documentation

Use class:
```
use CorepulseCacheBundle\Cache;
...

    public function indexAction(Cache $cache): 
    {
      ...
    }
```
- string $url : url của trang cache.
- string $view : nội dung trả về của url.
- array $tags: nhãn của cache.
- string $type: loại file cache (`html` hoặc `json`).
```
	//Tạo cache theo url
	$cache->create($url, $view, $tags, $type);

	// tạo lại cache theo tags
	$cache->createByTags($tags);

	// xóa cache theo tags
	$cache->clearByTags($tags);

	// xóa cache
	$cache->clearAll();
```


## Update Nginx:
```
map $args ${project}_static_page_root {
    default                                 /var/tmp/cpcache;
    "~*(^|&)pimcore_editmode=true(&|$)"     /var/nonexistent;
    "~*(^|&)pimcore_preview=true(&|$)"      /var/nonexistent;
    "~*(^|&)pimcore_version=[^&]+(&|$)"     /var/nonexistent;
    "~*(^|&)cms_editmode=true(&|$)"     /var/nonexistent;
}

map $uri ${project}_static_page_uri {
    default                                 $uri;
    "/"                                     "";
}
```

```
server {
    ... 
    
     location / {
        error_page 404 /meta/404;

        try_files ${project}_static_page_root${project}_static_page_uri/index.html $uri /index.php$is_args$args;
    }
    
    ...
}
```
