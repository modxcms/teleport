### Teleport 2.0.2 (2022-03-18)

* Change phar compilation to include all vendor php files

### Teleport 2.0.1 (2022-03-15)

  * Update phar compilation to add missing composer files

### Teleport 2.0.0 (2022-03-14)

  * Fix FileVehicle paths (#40)
  * Replace each() with foreach() for PHP 8 compatibility (#39)
  * Prevent Transport::postInstall() from clearing session settings

### Teleport 1.6.0 (2019-11-21)

  * Allow base option to override current working directory

### Teleport 1.5.0 (2016-03-01)

  * Make sure xPDOObjectCollection sets are ordered
  * Add form_customizations Extract tpl
  * Skip MySQLVehicle if SELECT stmt fails on Extract
  * Add missing modAccessNamespace to promote.tpl.json
  * Fix invalid paths in promote.tpl.json

### Teleport 1.4.0 (2016-01-17)

  * Add template Extract tpl to package a Template by templatename
  * Add ability to provide signature for package created by Extract
  * Add Workspace/GC Action for cleaning up workspace/ directory

### Teleport 1.3.0 (2015-08-10)

  * Fix Extract warnings when no attributes exist in the tpl
  * Make AWS a suggested package and include only needed react packages
  * Allow a MODX instance to be explicitly set on the Teleport instance
  * Switch order of posix user and group switching attempts

### Teleport 1.2.0 (2015-08-09)

  * Add ability to include package attributes in an Extract tpl

### Teleport 1.1.0 (2015-07-19)

  * Skip vehicles referencing classes not available in specific MODX releases
  * Add promote Extract tpl

### Teleport 1.0.0 (2015-02-17)

  * Ensure MODX available in changeset callback functions
  * Use DIRECTORY_SEPARATOR for Windows compatibility
  * Add Teleport\Transport\FileVehicle using Finder and Filesystem
  * Add vehicle_parent_class support to Teleport\Transport\Transport
  * Refactor posix user switching to use user argument

### Teleport 1.0.0-alpha4 (2013-12-10)

  * Add resource_children Extract tpl
  * Refactor tpl parsing to occur before json_decode
  * Add Packages/GC Action
  * Allow tplBase arg to override value for Extract
  * Run APIRequests in sub-process to avoid constant conflicts
  * Add HTTP server to handle teleport web requests
  * Add Pull Action
  * Remove dependency on MODX in Push Action
  * Add support for Actions from other namespaces via namespace argument
  * Add RequestInterface::request() to call actions as sub-requests

### Teleport 1.0.0-alpha3 (2013-11-05)

  * Add teleport-tpl-update option to toggle update of tpl copies in projects using as library
  * Add teleport-tpl-dir option to composer.json extra section
  * Add missing dependencies in phar Compilation
  * Refactor Compiler for creating phars when using Teleport as a library


### Teleport 1.0.0-alpha2 (2013-11-02)

  * Attempt to create profile/ and workspace/ dirs automatically
  * Fix tpl script paths by using a tplBase from the specified tpl arg


### Teleport 1.0.0-alpha1 (2013-11-02)

  * Initial release
