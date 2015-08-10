### Teleport 1.2.1 (TBD)

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
