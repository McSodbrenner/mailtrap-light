# mailtrap-light
**A collection of tiny PHP scripts (_all together below 12kb!_) to provide a hassle-free development environment for testing emails.**

It consists of a sendmail replacement, an SMTP server and a POP3 server. These scripts are probably neither the fastest nor the most feature packed, but that shouldn't matter in a local environment. They should just help you to test the email delivery

* without the risk of spamming your customers
* without the need of adapting your application
* without the need of an internet connection.

It works like the webservice [mailtrap.io](https://mailtrap.io/) but much more simpler.

You will first have a receiver script (sendmail replacement or SMTP server) and then a forwarder script (to save the mail as file to catch the mails via the POP3 server or to forward the mail by SMTP to your default mail account). Take a look at the examples folder to see the possibilities.

** No mails will ever be delivered to your customers! You will get all mails for all receiver email addresses. Nobody else. **


## The `sendmail` replacement
The `mail()` command doesn't work in your local development environment? No problem.

 1. Copy `examples/sendmail` to a different path and edit it to suit your needs.
 2. Edit your `php.ini` and set `sendmail_path` to `YOUR_PATH/sendmail`.
 3. Restart your webserver.


## The SMTP server
You need an SMTP server because your application needs on? No problem.

 1. Copy `examples/smtp-server.php` to a different path and edit it to suit your needs.
 2. Start the server with `php smtp-server.php` on your console. At the console you can watch the exchanged traffic.
 3. Set the following parameters for your SMTP client library:
     * **Host:** `IP` or `localhost`
	 * **Port:** `10025`
	 * **Username:** _Not needed_
	 * **Password:** _Not needed_


## The POP3 server
You don't have an internet connection? No problem.

 1. Set up the SMTP server with the forwarder class `ForwardToFile`.
 2. Copy `examples/pop3-server.php` to a different path and edit it to suit your needs.
 3. Start the server with `php pop3-server.php` on your console. At the console you can watch the exchanged traffic.
 4. Configure your local mail client:

	 ** POP3 **
     * **Host:** `IP` or `localhost`
	 * **Port:** `10110`
	 * **Username:** _Whatever you want_
	 * **Password:** _Whatever you want_

	** SMTP **
     * **Host:** `IP` or `localhost`
	 * **Port:** `10025`
	 * **Username:** _Not needed_
	 * **Password:** _Not needed_
