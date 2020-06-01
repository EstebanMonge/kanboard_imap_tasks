Kanboard IMAP Tasks
=====================


- Create tasks by email fetched from IMAP

Author
------

- Esteban Monge
- License MIT

Based in the work of Frédéric Guillot from Mailgun plugin hosted on https://github.com/kanboard/plugin-mailgun
and the work of polom from kanboard-tasksbymail scripts hosted on https://github.com/polom/kanboard-tasksbymail

Requirements
------------

- Kanboard >= 1.0.33
- PHP 5.6 or >= 7.0.0

Installation
------------

You have the choice between 2 methods:

1. Download the zip file and decompress everything under the directory `plugins/Imap`
2. Clone this repository into the folder `plugins/Imap`

Finally add a crontab with this line:

	* * * * *  /path/to/kanboard/plugins/Imap/cron.php /path/to/kanboad/data/db.sqlite

Note: Plugin folder is case-sensitive.

Configuration
-------------

Go to Kanboard -> Settings -> Integrations
Go to Kanboard -> Project -> Menu -> Settings -> Edit project and add an Identifier

Testing
-------

You can send an email with the format _happykanboard+PROJECTIDENTIFIER@riseup.net_. The email subject will become the task title, and the email body that will become the task content.

You can also send an email with this format in subject: _Finish Duke Nukem Forever 2 \<PROJECTIDENTIFIER\> please!_ The task title will be "Finish Duke Nukem Forever 2 please!", and details in the email body that will be the task content.
