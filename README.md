# MultipleIconFontsBundle

SymfonyBundle using multiple icon fonts + Twig Function

Currently bundled icon-font-sets:

   - [FontAwesome]
   - [Glyphicons]
   - [MaterialIcons]
   - [Ionicons]
   

## Usage:
Unitl a tagged release is published use:
```sh
composer require nickolaus/multiple-icon-fonts-bundle:dev-master
```

Or config via composer.json
``` json
{
    "require": {
        "nickolaus/multiple-icon-fonts-bundle": "dev-master"
    }
}
```

#### Twig function calls:
```twig
{{ icon('title', 'fontset', 'icon-attributes' }}
```
```twig
{{ icon_fontawesome('title', 'icon-attributes' }}
```
```twig
{{ icon_glyphicon('title', 'icon-attributes' }}
```
```twig
{{ icon_material_design('title', 'icon-attributes' }}
```
```twig
{{ icon_ionicons('title', 'icon-attributes' }}
```
All `icon-attributes` are prefixes with `icon-`

#### Valid icon attributes are:
   - in progress....


### Planned Features:
   - octicons intetration
   - foundation-icons interation


### Known Issues:

   - css classes for rotating are not working on all font-sets
 

### Thanks to:
  - [@font-awesome]
  - [@bootstrap]
  - [@mervick]
  - [@driftyco]
 


   [FontAwesome]: <https://github.com/components/font-awesome>
   [Glyphicons]: <https://github.com/twbs/bootstrap>
   [MaterialIcons]: <https://github.com/mervick/material-design-icons>
   [Ionicons]: <https://github.com/driftyco/ionicons>
   [@bootstrap]: <http://getbootstrap.com/>
   [@font-awesome]: <http://fontawesome.io/>
   [@mervick]: <https://github.com/mervick>
   [@driftyco]: <https://github.com/driftyco>
