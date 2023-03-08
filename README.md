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
Only mandatory configuration parameter is `index_template` -> file to include as response when backend has finished routing check.  
```yaml
site_tree:
    index_template: index.html
```
```php
use Symfony\Config\SiteTreeConfig;

return static function (SiteTreeConfig $config): void {
    $config
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
    index_template: index.html
    types:
        news: ~
        news2:
            entity: App\Entity\NotNews
```
```php
use Symfony\Config\SiteTreeConfig;
use App\Entity\NotNews;

return static function (SiteTreeConfig $config): void {
    $config
        ->indexTemplate('index.html');
        
    $config->types('news');
    // or exact entity class if it is not default App\Entity\News
    $config->types('news2')->entity(NotNews::class);
};
```
---
### Usage
To add new site tree node, call site tree api. default: POST `/api/site_trees`:
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
    "parent": "/api/site_trees/1"
}
```
To query all nodes, call GET `/api/site_trees` or to query just roots with
children, call GET `/api/site_trees?level=0`

**Additionally** this package provides a check if url is defined in any node. To check if url is valid, call
GET `/api/content_types/<url>`.  
For example, from example above, GET `/api/content_types/test` would return:
`200 OK` + 
```json
{
    "nodeId": 1,
    "node": "/api/site_trees/1",
    "type": "resource"
}
```
but GET `/api/content_types/test2` would return:
`404 Not Found` + 
```json
{
    "type": "https://tools.ietf.org/html/rfc2616#section-10",
    "title": "An error occurred",
    "detail": "Not Found"
}
```
