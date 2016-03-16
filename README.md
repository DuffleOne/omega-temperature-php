# omega-temperature-php
PHP script that connects to the Omega dual channel temperature sensor over a socket.

## How to?
run `php epip.php {ip} {port?}` on the command line.
Default port is 2000.

## Omega Settings
Under "Terminal Server", set the following settings:
* TCP/UDP: TCP
* Server Type: Continuous
* Forward CR: disable
* Number of Connections: 3
* Port: 02000
* Disconnect After Data Sent: True (Checked)
