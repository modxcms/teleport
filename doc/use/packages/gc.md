# Packages/GC


## Package Garbage Collection

You can remove outdated packages from a profiled MODX deployment using the following command:

    php teleport.phar --action=Packages/GC --profile=profile/mysite.profile.json


## Garbage Collection for MODX Package Management

### Required Arguments

* `--profile=path` - A valid stream path to a Teleport [Profile](../profile.md) defining the MODX instance this action is to be performed against.

### Optional Arguments

* `--preserveZip` - Prevents removal of the zip files for the outdated packages, removing only the extracted package directories and database records.
