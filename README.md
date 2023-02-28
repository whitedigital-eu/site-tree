# SiteTree

### What is it?
This package adds site tree functionallity to api platform with addition of
backend check of route validity when used together with separate frontend like vue.

### System Requirements
PHP 8.2+  
Symfony 6.2+

### Installation
The recommended way to install is via Composer:

```shell
composer require whitedigital-eu/site-tree
```
---

### Configuration
By default this bundle comes disabled after installation. To enable it you need to 
configure file to be included after package checks if opened url is valid tree branch.
```yaml
site_tree:
    enabled: true
    index_template: index.html
```
```php
use Symfony\Config\SiteTreeConfig;

return static function (SiteTreeConfig $config): void {
    $config
        ->enabled(true)
        ->indexTemplate('index.html');
};
```
> **IMPORTANT**: File given to this parameter must be in path configured for twig bundle

After this, you need to update your database schema to use Audit entity.  
If using migrations:
```shell
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```
If by schema update:
```shell
bin/console doctrine:schema:update --force
``` 
---
By default this package comes with 2 predefined types to be used as site tree nodes: `Html` and `Redirect`.  
`Html` is a straight forward type without any extra logic, just plain html content.  
`Redirect` is a type that manages redirects between nodes or to external sources.

To add new type, you need:
1. Create new Entity and Resource for this type
2. Configure type in configuration:
```yaml
site_tree:
    enabled: true
    index_template: index.html
    types:
        news:
            entity: ~
        news2:
            entity: App\Entity\NotNews
```
```php
use Symfony\Config\SiteTreeConfig;
use App\Entity\NotNews;

return static function (SiteTreeConfig $config): void {
    $config
        ->enabled(true)
        ->indexTemplate('index.html');
        
    $config->types('news');
    // or exact entity class if it is not default App\Entity\News
    $config->types('news2')->entity(NotNews::class);
};
```
---
### Usage
To add new site tree node, call site tree api. default: POST `/api/wd/st/site_trees`:
```json
{
    "title": "test",
    "slug": "test",
    "type": "html"
}
```
This is the minimal data to create a new node. As no parent is given, this call will
create a new root node. Normally site would need only one node, but if there are
multiple menus or other links that require new nodes, like, `login`, for example,
it is possible to do with this package.  
If you want to add node to other node, simply add `parent`parameter:
```json
{
    "title": "test",
    "slug": "test",
    "type": "html",
    "parent": "/api/wd/st/site_trees/1"
}
```
To query all nodes, call GET `/api/wd/st/site_trees` or to query just roots with
children, call GET `/api/wd/st/site_trees?level=0`

**Additionally** this package provides a check if url is defined in any node. To check if url is valid, call
GET `/api/wd/st/content_types/<url>`.  
For example, from example above, GET `/api/wd/st/content_types/test` would return:
`200 OK` + 
```json
{
    "nodeId": 1,
    "node": "/api/wd/st/site_trees/1",
    "type": "resource"
}
```
but GET `/api/wd/st/content_types/test2` would return:
`404 Not Found` + 
```json
{
    "type": "https://tools.ietf.org/html/rfc2616#section-10",
    "title": "An error occurred",
    "detail": "Not Found"
}
```
---
**Overriding api resource options**
> **WARNING**: This overrides only configuration defined in `#ApiResource` attribute!

For example, if you want to override any option defined within `ApiResource` attribute on api resource defined in
`WhiteDigital\SiteTree\ApiResource\SiteTree` you can do it with using `ExtendedApiResource` attribute.  
For example, to override `routePrefix` to get iri of `/api/site_trees` instead of default `/api/wd/st/site_trees` do:
1. Create new class that extends resource you want to override
2. Add `ExtendedApiResouce` attribute insted of `ApiResource` attribute
3. Pass only those options that you want to override, others will be taken from resource you are extending
```php
<?php declare(strict_types = 1);

namespace App\ApiResource;

use WhiteDigital\ApiResource\Attribute\ExtendedApiResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource as WDSiteTreeResource;

#[ExtendedApiResource(routePrefix: '')]
class SiteTreeResource extends WDSiteTreeResource
{
}
```
`ExtendedApiResouce` attribute checks which resource you are extending and overrides options given in extension,
keeping other options same as in parent resource.

> **IMPORTANT**: You need to disable bundled resource in configuration, otherwise you will have 2 instances of audit
> resource: one with `/api/site_trees` iri and one with `/api/wd/st/site_trees` iri.

```yaml
whitedigital:
    audit:
        enabled: true
        enable_audit_resource: false
```
```php
use Symfony\Config\WhitedigitalConfig;

return static function (WhitedigitalConfig $config): void {
    $config
        ->audit()
            ->enabled(true)
            ->enableAuditResource(false);
};
```
---
