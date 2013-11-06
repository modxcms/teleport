# UserCreate


## Create a MODX User Account

You can create a user in a profiled MODX site using the following command:

    php teleport.phar --action=UserCreate --profile=profile/mysite.profile.json --username=superuser --password=password --sudo --active --fullname="Test User" --email=testuser@example.com


## The UserCreate Action

### Required Arguments

* `--profile=path` - A valid stream path to a Teleport [Profile](profile.md). This defines the MODX instance the UserCreate is to be performed against.
* `--username=string` - A valid MODX username.
* `--email=email` - A valid email address for the user.

### Optional Arguments

* `--password=string` - A valid MODX password for the user. If not specified, MODX will generate one and return the value from the command.
* `--fullname='string'` - An optional full name for the user.
* `--active` - Indicates if the user should be marked active when created.
* `--sudo` - Indicates if the user should be created as a sudo user.

_NOTE: This Action uses the security/user/create processor from the MODX site in the specified profile to create a user. It accepts any additional arguments that the processor does._
