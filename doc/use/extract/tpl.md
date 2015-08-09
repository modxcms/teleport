# Extract tpls

Extract tpls are JSON templates defining what, when, and how various objects, scripts, or file artifacts are to be extracted and packaged into a Teleport transport package.


## The properties of an Extract tpl

Each Extract tpl defines the attributes and vehicles that make up the manifest of a Teleport transport package. The manifest defines what is packaged and in what order.

### name

Every tpl has a name property which helps identify the tpl used to create a transport package. This name is included in the generated package filename.

### attributes

The attributes of a Teleport transport package can be set to make distributable packages that can be installed from within the MODX package management interface. You can set readme, changelog, and license attributes, as well as the `requires` attribute supported in MODX >=2.4 to specify package dependencies.

Note that you can also define an attribute as an object with the properties `sourceType` and `source`. For now, `fileContent` is the only supported `sourceType`, which uses the `source` attribute value as a file path or stream URL from which to read the value of the attribute.

    "attributes": {
        "changelog": {
            "sourceType": "fileContent",
            "source": "{+properties.modx.core_path}components/test/changelog.txt"
        },
        "requires": {
            "collections": "~3.0"
        }
    }

This makes it possible to use Teleport to create Extras packages that are ready to install from within MODX. Care will just need to be taken not to use any Teleport-specific `vehicle` classes in the packages created for this purpose.

### vehicles

Teleport transport vehicles define artifacts that are to be packaged when the tpl is used in an Extract action. They can be core xPDOVehicle classes or they can be Teleport-specific or even custom implementations which extend any of the core xPDOVehicle classes.

For example, the following defines a single vehicle that packages files from a specified source into a specified target:

    "vehicles": [
        {
            "vehicle_class": "xPDOFileVehicle",
            "object": {
                "source": "{+properties.modx.core_path}components/test",
                "target": "return MODX_CORE_PATH . 'components';"
            },
            "attributes": {"vehicle_class": "xPDOFileVehicle"}
        }
    ]

Or here is a more complex definition that packages all system and context settings from the MODX database:

    "vehicles": [
        {
            "vehicle_class": "xPDOObjectVehicle",
            "object": {
                "class": "modSystemSetting",
                "criteria": [
                    "1 = 1"
                ],
                "package": "modx"
            },
            "attributes": {
                "preserve_keys": true,
                "update_object": true
            }
        },
        {
            "vehicle_class": "xPDOObjectVehicle",
            "object": {
                "class": "modContextSetting",
                "criteria": [
                    "1 = 1"
                ],
                "package": "modx"
            },
            "attributes": {
                "preserve_keys": true,
                "update_object": true
            }
        }
    ]


## Included tpls

 * `changeset.tpl.json` - Extract a defined set of changes being recorded by callback functions in the MODX configuration.
 * `complete.tpl.json` - Extract all core objects, files, and custom database tables from a MODX deployment for replacing an entire deployment.
 * `complete_db.tpl.json` - Extract all core objects and custom database tables from a MODX deployment.
 * `develop.tpl.json` - Extract all core objects, files, and custom database tables from a MODX deployment to inject into another deployment, supplementing existing objects and custom tables.
 * `elements.tpl.json` - Extract all Elements and related data from a MODX deployment to inject into another deployment, updating and supplementing existing Elements.
 * `packages.tpl.json` - Extract all Packages registered in a MODX deployment to inject into another deployment.
 * `promote.tpl.json` - Extract core objects, files, and custom database tables except settings from a MODX deployment to inject into another deployment.
 * [`resource_children.tpl.json`](tpl/resource_children.md) - Extract all Resources that are children of a specified parent Resource.
 * `resources.tpl.json` - Extract all Resources from a MODX deployment to inject into another deployment, updating and supplementing existing Resources.
 * `settings.tpl.json` - Extract all Settings from a MODX deployment to inject into another deployment, updating and supplementing existing Settings.
 * `user.tpl.json` - Extract a single User and related data from a MODX deployment to inject into another deployment.
 * `users.tpl.json` - Extract all Users from a MODX deployment to inject into another deployment, updating and supplementing existing Users.


## Custom tpls

You can create and use your own custom tpls with Teleport. See [Extending Teleport with Custom Extract Tpls](../../extend/custom-extract-tpls.md) to get started.
