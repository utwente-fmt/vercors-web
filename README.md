# VerCors Website
This repository contains the website of VerCors. It is based on Yii, a PHP framework. Yii and our other dependencies are installed through [composer](https://getcomposer.org/), a dependency manager for PHP.

## Adding news
Go to the bottom of the VerCors website and click __Backoffice__. Log in if necessary, then click __News Items__. Some things to keep in mind: on the front page the last five news items are displayed, ordered by date. You need to enter the date in the format `yyyy-mm-dd`. The content is in markdown format, but also supports a limited subset of HTML (e.g. `a`).

## Modifying pages
For modifying pages other than news posts, you can edit the files in `views/static/*.php`. If you are confused about which page to edit, you can follow the trail from a url as follows:

 * Note the URL of the page you want to edit
 * Check in `config/environments/web.php` which _action_ corresponds to the URL. It should be in the format `controller/action`.
 * Go to the `controllers` directory, and open the controller corresponding to your action. e.g. for `static/index`, the controller would be `StaticController`.
 * Go to the action corresponding to your action. E.g. for `static/index`, the action would be `actionIndex`.
 * Finally, check the first argument of `$this->render`. This is the name of the view that correponds to the URL.
 * Your view will be located at `views/<controller>/<name>`. For the example, if `actionIndex` renders a view called `index`, it will be located at `views/static/index`.
 
 