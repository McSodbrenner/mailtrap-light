# mailtrap-light
A simple PHP script that acts as an SMTP server and a POP3 Server. Emails sent by your development environment will never leave it.

## Introduction
The simple PHP script will set up two very simple Servers. An STMP server at port 10025 and a POP3 server at port 10110. Both are neither fast nor secure, but this shouldn't matter at a local environment. They should just help you to test the email delivery without the risk of spamming your customers. It works a little like the webservice mailtrap.io but much more simpler.

For the SMTP server you just have to configure "localhost" or your IP as SMTP host and port 10025. Username and password are not required. The same for the POP3 Server. Just define your local host name and change the port. Use as username and password whatever you like.

It is very basic. So it should be very simple to adapt it.

## Installation
`php index.php`
At the console you can watch the exchanged traffic.
