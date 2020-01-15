Simple ActiveRecord config for ILIAS plugins

### Usage

#### Composer
First add the following to your `composer.json` file:
```json
"require": {
  "srag/activerecordconfig": ">=0.1.0"
},
```

And run a `composer install`.

If you deliver your plugin, the plugin has it's own copy of this library and the user doesn't need to install the library.

Tip: Because of multiple autoloaders of plugins, it could be, that different versions of this library exists and suddenly your plugin use an older or a newer version of an other plugin!

So I recommand to use [srag/librariesnamespacechanger](https://packagist.org/packages/srag/librariesnamespacechanger) in your plugin.

#### Using trait
Your class in this you want to use Config needs to use the trait `ConfigTrait`
```php
...
use srag\ActiveRecordConfig\x\Utils\ConfigTrait;
...
class x {
...
use ConfigTrait;
...
```

#### Config ActiveRecord
First you need to init the `Config` active record classes with your own table name prefix. Please add this very early in your plugin code
```php
self::config()->withTableName(ilXPlugin::PLUGIN_ID . "_config")->withFields([]);
```

Add an update step to your `dbupdate.php`
```php
...
<#x>
<?php
\srag\ActiveRecordConfig\x\Repository::getInstance()->installTables();
?>
```

and not forget to add an uninstaller step in your plugin class too
```php
self::config()->dropTables();
```

### More infos
[Old readme - deprecated!!!](./doc/OldREADME.md)

### Requirements
* ILIAS 5.3 or ILIAS 5.4
* PHP >=7.0

### Adjustment suggestions
* External users can report suggestions and bugs at https://plugins.studer-raimann.ch/goto.php?target=uihk_srsu_ACCONF
* Adjustment suggestions by pull requests via github
* Customer of studer + raimann ag: 
	* Adjustment suggestions which are not yet worked out in detail by Jira tasks under https://jira.studer-raimann.ch/projects/ACCONF
	* Bug reports under https://jira.studer-raimann.ch/projects/ACCONF
