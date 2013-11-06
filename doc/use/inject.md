# Inject


## Inject a Teleport Package

You can Inject a Teleport package from any valid stream source into a MODX site using the following command:

    php teleport.phar --action=Inject --profile=profile/mysite.profile.json --source=workspace/mysite_develop-120315.1106.30-2.2.1-dev.transport.zip

_NOTE: If the source is not within the workspace/ directory a copy will be pulled to that location and then removed after the Inject completes unless --preserveWorkspace is passed._

### How Inject Manipulates Snapshots

To prevent some data from corrupting a target MODX deployment when it is injected, the Inject action takes the following measures:

* Before Injection
    * modSystemSetting vehicles with the following keys are removed from the manifest:
        * `session_cookie_domain`
        * `session_cookie_path`
        * `new_file_permissions`
        * `new_folder_permissions`
* After Injection
    * modSystemSetting `settings_version` is set to the actual target version.
    * modSystemSetting `session_cookie_domain` is set to empty.
    * modSystemSetting `session_cookie_path` is set to `MODX_BASE_PATH`.


## The Inject Action

### Required Arguments

* `--profile=path` - A valid stream path to a Teleport [Profile](profile.md). This defines the MODX instance the Inject is to be performed against.
* `--source=path` - A valid stream path to a Teleport package to Inject into the MODX instance described by the specified Profile.

### Optional Arguments

* `--preserveWorkspace` - Indicates if the workspace/ copy of the package should be removed after being pushed to a target.
