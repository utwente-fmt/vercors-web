# VerCors Website
This repository contains the code and data of the VerCors website. It is generated as a set of static pages.

## Building
The website is built by `build.py`. To pull in the dependencies, e.g. set up a virtualenv:

```bash
$ virtualenv venv -p python3
$ source venv/bin/activate
$ pip3 install -r requirements.txt
```

Then build the website:
```bash
$ python3 build.py
```

You can also run a debug version of the website to inspect your edits, though it does not automatically update on save:

```bash
$ python3 test.py
Building the website...
Now serving on http://localhost:8000/
```

## Structure
* `/build` contains the statically rendered website after building;
* `/generated_templates` contains generated jinja templates after building;
* `/data` contains the website data structured as [toml](https://toml.io/en/v1.0.0);
* `/templates` contains html templates rendered with [jinja2](https://jinja.palletsprojects.com/en/3.1.x/);
* `/static` contains other resources, and is copied as is.

Generally the process of building is as follows:
* `urls` is loaded from `/data/urls.toml`
* `data` is constructed by loading the other files in `/data`
* `pages` couples entries in `urls` to template files
* `/build` and `/generated_templates` are deleted
* The templates for the wiki and wiki menu are rendered into `/generated_templates`
* Everything in `pages` is rendered with `data` as context, plus any additional arguments from `pages`
  * If the URL ends in `/`, `index.html` is appended automatically
* `/static/**/*` is copied to `/build`

## Add a page
* Make a new entry in `/data/urls.toml`
* Make a new entry in `build.build.pages`
* If need be, load more data into `data` if you want a separate toml file in `/data`
