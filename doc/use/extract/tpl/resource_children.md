# resource_children.tpl.json

This tpl extracts all Children of a specified parent Resource recursively.

The following example command would extract all Children of the Resource with an id of 2.

    php teleport.phar --action=Extract --profile=profile/mysite.profile.json --tpl=phar://teleport.phar/tpl/resource_children.tpl.json --parent=2

This does not include the parent Resource in the Extract.


## Arguments

### Required

* `--parent=id` - Indicates the id of the parent Resource to extract children from.
