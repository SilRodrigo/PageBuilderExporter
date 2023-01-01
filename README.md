# PageBuilderExporter Module for Magento 2

[![Latest Stable Version](https://img.shields.io/packagist/v/rsilva/pagebuilder-exporter.svg?style=flat-square)](https://packagist.org/packages/opengento/module-document)
[![License: MIT](https://img.shields.io/github/license/opengento/magento2-document.svg?style=flat-square)](./LICENSE) 
[![Packagist](https://img.shields.io/packagist/dt/rsilva/pagebuilder-exporter.svg?style=flat-square)](https://packagist.org/packages/rsilva/pagebuilder-exporter/stats)
[![Packagist](https://img.shields.io/packagist/dm/rsilva/pagebuilder-exporter.svg?style=flat-square)](https://packagist.org/packages/rsilva/pagebuilder-exporter/stats)

This module allows you to import and export pagebuilder templates.

 - [Setup](#setup)
   - [Composer installation](#composer-installation)
   - [Setup the module](#setup-the-module)
 - [Features](#features)
 - [Using](#using)
 - [Support](#support)
 - [Authors](#authors)
 - [License](#license)

## Setup

Magento 2 Open Source or Commerce edition is required.

### Composer installation

Run the following composer command:

```
composer require rsilva/pagebuilder-exporter
```

### Setup the module

Run the following magento command:

```
bin/magento module:enable Rsilva_PageBuilderExporter
bin/magento setup:upgrade
```

**If you are in production mode, do not forget to recompile and redeploy the static resources.**

## Features

Export PageBuilder templates to json files.
Import it back to your store with a few clicks.

## Using

### Exporting

 1. Access your Magento admin panel.
 2. On left menu access Content -> Templates
 3. On your PageBuilder templates list
 4. Choose a template you'd like, on action column select ***Export*** option
 5. Your download will start immediately
 
### Importing

 1. Access your Magento admin panel.
 2. On left menu access Content -> Templates
 3. Click on **Import Template** button
 4. Choose a *valid* exported template file
 5. Click on Import
 6. That's it!

## Support

Send a Hi to rodrigo.sil91@gmail.com and I will try to help.

## Author

- **Rodrigo Silva** - *Maintainer* - [![GitHub followers](https://img.shields.io/github/followers/SilRodrigo.svg?style=social)](https://github.com/SilRodrigo)


## License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) details.

***That's all folks!***
