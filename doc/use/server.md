# Teleport HTTP Server

The Teleport HTTP Server is an HTTP server you can run to listen to requests for Teleport Actions on a particular port.


## Running the HTTP Server

You can run the Teleport HTTP Server very easily by executing the `bin/server.php` or `bin/server` scripts included with Teleport and specifying the port you want to run the server on.

    bin/server 8082

Or...

    php bin/server.php 1337 --verbose

This starts the Teleport HTTP listener and allows execution of Teleport Actions over the HTTP protocol. The `--verbose` option makes Teleport output useful information to stdout from the server.

## Executing Teleport Actions on the Server

Calling a Teleport Action on the server takes the form:

    http://hostname:port/Action?arg1=value&arg2=1&arg3=value2

For example, to run an Extract:

    http://localhost:1337/Extract?profile=profile/test_profile.profile.json&tpl=tpl/complete.tpl.json

