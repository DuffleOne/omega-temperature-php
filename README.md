# omega-temperature-php
PHP script that connects to the Omega dual channel temperature sensor over a socket.

## How to?
run `php index.php {ip} {port?}` on the command line.
Default port is 2000.

You can also refer to `index.php` on how to run this within your own script.

It's important to know that because the box does not respond instantly, this script can take a few seconds to run.
If you are running this on the web, best run it as a seperate task and cache/database the results.

## Omega Settings
Via the web interface for the Omega box, go to Configuration.
Under "Terminal Server", set the following settings:

* TCP/UDP: TCP
* Server Type: Continuous
* Forward CR: disable
* Number of Connections: 3
* Port: 02000
* Disconnect After Data Sent: False (Unchecked)
