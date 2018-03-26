[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FEstebanMonge%2Fkanboard_imap_tasks.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FEstebanMonge%2Fkanboard_imap_tasks?ref=badge_shield)

Kanboard IMAP Tasks
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
Go to Kanboard -> Project -> Menu -> Settings -> Edit project and add a Identifier

Testing
-------

You can send a email with this format happykanboard+PROJECTIDENTIFIER@riseup.net put a subject that will be the task title and details in the email body that will be the task content.

You can also send a email with this format in subject: Finish Duke Nukem Forever 2 \<PROJECTIDENTIFIER\> please! Finish Duke Nukem Forever 2 please! will be the title and details in the email body that will be the task content.


## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FEstebanMonge%2Fkanboard_imap_tasks.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FEstebanMonge%2Fkanboard_imap_tasks?ref=badge_large)