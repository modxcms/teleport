# Extract

The Teleport Extract Action is an extremely flexible transport packaging tool for MODX Revolution.


## Extract a Teleport Package from a MODX Site

You can Extract a Teleport package from a MODX site using the following command:

    php teleport.phar --action=Extract --profile=profile/mysite.profile.json --tpl=phar://teleport.phar/tpl/complete.tpl.json

The package will be located in the workspace/ directory if it is created successfully.

You can also Extract a Teleport package and push it to any valid stream target using the following command:

    php teleport.phar --action=Extract --profile=profile/mysite.profile.json --tpl=phar://teleport.phar/tpl/complete.tpl.json --target=s3://mybucket/snapshots/ --push

In either case, the absolute path to the package is returned by the process as the final output. You can use this as the path for an Inject source.

_NOTE: The workspace copy is removed after it is pushed unless you pass --preserveWorkspace to the CLI command._


## The Extract Action

### Required Arguments

* `--profile=path` - A valid stream path to a Teleport [Profile](profile.md). This defines the MODX instance the Extract is to be performed against.
* `--tpl=path` - A valid stream path to a Teleport [Extract tpl](extract/tpl.md) which defines the extraction process.

### Optional Arguments

* `--target=path` - A valid stream path to a folder where the extracted package should be pushed.
* `--push` - Indicates if the extracted package should be pushed to the target.
* `--preserveWorkspace` - Indicates if the workspace/ copy of the package should be removed after being pushed to a target.

_NOTE: Individual Extract tpls can use any additional arguments passed to the command line as value replacements in the tpl. See documentation for specific tpls to see what additional arguments, if any, that tpl supports._
