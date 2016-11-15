Tasks Estimated Dates
=====================


- Create tasks by mails fetched from IMAP

Author
------

- Esteban Monge
- License MIT

Based in the work of Frédéric Guillot from Mailgun plugin hosted on https://github.com/kanboard/plugin-mailgun
and the work of polom from kanboard-tasksbymail scripts hosted on https://github.com/polom/kanboard-tasksbymail

Requirements
------------

- Kanboard >= 1.0.33
- PHP >= 7.0.0

Installation
------------

You have the choice between 2 methods:

1. Download the zip file and decompress everything under the directory `plugins/Imap`
2. Clone this repository into the folder `plugins/Imap`

Note: Plugin folder is case-sensitive.

Add a crontab with this line:

	* * * * *  /path/to/kanboard/plugins/Imap/cron.php /path/to/kanboad/data/db.sqlite

Configuration
-------------

Go to Kanboard -> Settings -> Integrations
Go to Kanboard -> Project -> Menu -> Settings -> Edit project and add a Identifier

Testing
-------

You can send a email with this format happykanboard+PROJECTIDENTIFIER@riseup.net put a subject that will be the task title and details in the email body that will be the task content.

You can also send a email with this format in subject: Finish Duke Nukem Forever 2 <PROJECTIDENTIFIER> please! Finish Duke Nukem Forever 2 please! will be the title and details in the email body that will be the task content.
