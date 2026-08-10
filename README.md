# VerCors Website
This repository contains the code and data of the VerCors website. It is generated as a set of static pages.

## Building \& Testing
First the dependencies must be pulled in. For python we recommend using a [virtualenv](https://virtualenv.pypa.io/en/latest/how-to/install.html):
```bash
virtualenv venv -p python3
source venv/bin/activate
pip3 install -r requirements.txt
```

You also need a working `mdbook` command at version [0.5.4](https://github.com/rust-lang/mdBook/releases/tag/v0.5.4) for the wiki build, and (`node`)[https://nodejs.org/en/download/current] if you want to regenerate the Ace syntax highlighters.

### Building

Then build the website:
```bash
$ python3 build.py
```

### Testing
You can also run a debug version of the website to inspect your edits, though it does not automatically update on save:

```bash
$ python3 test.py
Building the website...
Now serving on http://localhost:8000/
```

**Note**: On Windows, you may have to explicitly use UTF-8 encoding, using `python.exe -X utf8 build.py` and `python.exe -X utf8 test.py`, respectively.


### Environment variables
The build scripts can be pointed at local VerCors checkouts with environment variables:

* `VERCORS_GIT` overrides the VerCors repository used to fetch examples and other VerCors sources. It can point to a local repository path or a git URL.
* `VERCORS_WIKI_GIT` overrides the wiki source used to generate the website's mdBook content. It can point to a local `vercors.wiki` checkout or a git URL.

If neither variable is set, the build uses the default upstream repositories.

## mdBook
The wiki section of the website is generated with [mdBook v0.5.4](https://github.com/rust-lang/mdBook/releases/tag/v0.5.4), and that version is required to build the website.

The build process renders the wiki into the mdBook project under `wiki_book/`, runs `mdbook build`, and then copies the resulting HTML into the website build output. If `mdbook` is missing or a different version is installed, the wiki portion of the website may fail to build correctly.

> [!WARNING]
> A lot of custimization has gone into the theme of the mdBook (see `wiki_book/theme`). This makes updating to a new version of mdBook harder so thread carefully if this needs to be done. As an alternative, the wiki could also be rendered without the standard header and footer from the VerCors site, which would require a lot less customization.

## Ace syntax highlighting
The website ships syntax highlighting files generated from [Ace](https://ace.c9.io/#nav=higlighter), using the fork at [sakehl/ace](https://github.com/sakehl/ace). The generated highlighter files are stored under `static/js/mode-*`.

If you add or change language keywords or other syntax rules, clone the ace fork, edit the relevant highlighter source, for example [`src/mode/spec_highlight_rules.js`](https://github.com/sakehl/ace/blob/master/src/mode/spec_highlight_rules.js), and then regenerate the Ace build artifacts with:

```bash
node ./Makefile.dryice.js -nc
```

The resulting files will be written to `build/src-noconflict`. Copy the relevant files again to `static/js/`.

## Structure
* `/build` contains the statically rendered website after building;
* `/generated_templates` contains generated jinja templates after building;
* `/data` contains the website data structured as [toml](https://toml.io/en/v1.0.0);
* `/templates` contains html templates rendered with [jinja2](https://jinja.palletsprojects.com/en/3.1.x/);
* `/wiki_book/src` contains the generated mdBook source for the wiki;
* `/wiki_book/book` contains the built mdBook HTML output for the wiki;
* `/generate_wiki.py` converts the wiki source into mdBook pages;
* `/static/js/mode-*` contains generated Ace syntax highlighter files;
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
