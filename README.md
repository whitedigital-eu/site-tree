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

### Extra configuration parameters
As this library does use route listener to override router when any url is called to include
index template file, you might need to filter out some paths where this should not be done.  
To configure these paths, you can do:
```yaml
site_tree:
    index_template: index.html
    excluded_path_prefixes:
        - '/admin'
    excluded_path_prefixes_dev:
        - '/test'
```
```php
use Symfony\Config\SiteTreeConfig;

return static function (SiteTreeConfig $config): void {
    $config
        ->indexTemplate('index.html')
        ->excludedPathPrefixes([
            '/admin',
        ])
        ->excludedPathPrefixesDev([
            '/test',
        ]);
};
```
This configuration skips listener logic for any path that starts with these definded paths.  
By default this bundle alrady skip some paths:  
In any environment:  
- `/api`
- `/sitemap.xml`

In dev/test environment:  
- `/_profiler`  
- `/_wdt`  
- `/_error`  

### For more advanced security, all custom content types should extend `AbstractContentTypeProvider`
If you have any custom content types that are accessed publicly, you should 
use `AbstractContentTypeProvider` as a base provider so poblicly you
can access only type data from active site tree nodes.
```php
use WhiteDigital\SiteTree\DataProvider\AbstractContentTypeProvider;

class CustomContentTypeDataProvider extends AbstractContentTypeProvider {
    // ...
}
```
If this extension is not possible, you can use `LimitContentTypePublicAccessTrait` to get limiter
function for use with `Doctrine\Orm\QueryBuilder` for collection and single items.

### Overriding parts of the bundle

**Overriding default api resources (and therefore api endpoints)**

By default, SiteTree bundle resources is based on `src/Api/Resource` directory contents.  
If you wish not to use these resources and not expose the api endpoints they provide, just set a custom api resource path
with a configuration value. If you set it as `null`, api platform will not register api resources located within this
package.

```yaml
site_tree:
    custom_api_resource_path: '%kernel.project_dir%/src/MyCustomPath'
#    custom_api_resource_path: null
```

```php
use Symfony\Config\SiteTreeConfig;

return static function (SiteTreeConfig $config): void {
    $config
        ->customApiResourcePath('%kernel.project_dir%/src/MyCustomPath')
        // or  ->customApiResourcePath(null);
};
```
After overriding default api resources, do not forget to update ClassMapperConfigurator configuration that is used for
resource <-> entity mapping in `whitedigital-eu/entity-resource-mapper-bundle`
```php
use App\ApiResource\Admin\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapper;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapperConfiguratorInterface;

final class ClassMapperConfigurator implements ClassMapperConfiguratorInterface
{
    public function __invoke(ClassMapper $classMapper): void
    {
        $classMapper->registerMapping(SiteTreeResource::class, SiteTree::class);
    }
}
```
---

### Sitemap

To add sitemap to `GET /sitemap.xml`, you need to add route configuration to project routes.
```yaml
# config/routes/site-tree.yaml
site_tree:
    resource: '../vendor/whitedigital-eu/site-tree/src/Controller/'
    type:     attribute
```
```php
// config/routes/site-tree.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('../vendor/whitedigital-eu/site-tree/src/Controller/', 'attribute');
};

```
Sitemap only returns enabled routes with `isActive: true` and `isVisible: true`. If you want to
include invisible (but still active routes) with `isActive: true` and `isVisible: false`, configure
it in configuration:
```yaml
site_tree:
    #...
    sitemap:
        include_invisible: true
```
```php
use Symfony\Config\SiteTreeConfig;

return static function (SiteTreeConfig $config): void {
    $config
        ->sitemap()
            ->includeInvisible(true);
};
```
