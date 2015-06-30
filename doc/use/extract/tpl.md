# Extract tpls

Extract tpls are JSON templates defining what, when, and how various objects, scripts, or file artifacts are to be extracted and packaged into a Teleport transport package.


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

