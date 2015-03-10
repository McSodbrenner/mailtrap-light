# mailtrap-light
**A collection of tiny PHP scripts (_all together below 12kb!_) to provide a hassle-free development environment for testing emails.**

It consists of a sendmail replacement, an SMTP server and a POP3 server. These scripts are probably neither the fastest nor the most feature packed, but that shouldn't matter in a local environment. They should just help you to test the email delivery

* without the risk of spamming your customers
* without the need of adapting your application
* without the need of an internet connection.

It works like the webservice [mailtrap.io](https://mailtrap.io/) but much more simpler.

You will first have a receiver script (sendmail replacement or SMTP server) and then a forwarder script (to save the mail as file to catch the mails via the POP3 server or to forward the mail by SMTP to your default mail account). Take a look at the examples folder to see the possibilities.


## The `sendmail` replacement
The `mail()` command doesn't work in your local development environment? No problem.



## The SMTP server
Start the server with a simple `php smtp.php` on your console. This will start the SMTP server on your local machine at port `10025` (you can change this in `smtp.php`). At the console you can watch the exchanged traffic.
In your application's configuration file set your IP or `localhost` as host and `10025` as port. You don't need to authenticate with Username or Password.

Take a look at the `$transports` array in the `smtp.php` file. There you can define what you want to do with the mail data the server receives.

* If you want to use the **POP3 Server** comment in the transport `SmtpToFile`. This will save the mail data on the file system. The POP3 server will deliver the mails from there. This transport is useful because you are able to test email delivery without internet access, because the emails do not leave your development machine.


* If you want to **forward the mails to a different SMTP server** comment in the transport `SmtpToSMTP`. This is useful if you just want to send emails from your development machine without the risk of committing your credentials to a versioning system like GIT or SVN.

## The POP3 server
Start the server with a simple `php pop3.php` on your console. This will start the POP3 server on your local machine at port `10110` (you can change this in `pop3.php`). At the console you can watch the exchanged traffic.
In your email client set `POP3` as protocol, your IP or `localhost` as host and `10025` as port. Choose whatever you want as Username or Password. You are always allowed to login. :)
All emails to email addresses will just be delivered to your mail account.
