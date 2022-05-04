import os
import shutil
import sys

import jinja2
import markdown
from jinja2 import FileSystemLoader

from util import *


def build():
    os.chdir(os.path.dirname(sys.argv[0]))

    shutil.rmtree("generated_templates", ignore_errors=True)
    os.mkdir("generated_templates")

    print("Loading the urls...")
    urls = load_data("urls")

    print("Loading other data...")
    data = {
        "urls": urls,
        "about": load_data("about"),
        "news": by_date_desc(titled(load_data("news"))),
        "examples": titled(load_data("examples")),
        "languages": load_data("languages"),
    }

    print("Rendering bibliography...")
    fetch_bibliography_into(data)

    print("Fetching example data from git repository...")
    fetch_examples_into(data, "v1.4.0")

    print("Rendering wiki...")
    fetch_wiki("dev")

    print("Computing routes and template data...")
    pages = {
        urls["index"]: ("index.html", {}),
        urls["about"]: ("about.html", {}),
        urls["publications"]: ("publications.html", {}),
        urls["news"]: ("news.html", {}),
        urls["examples"]: ("showcases.html", {}),
        urls["try_online"]: ("try_online.html", {}),
        urls["wiki"]: ("wiki.html", {}),
    }

    pages.update({
        urls["article"] % slugify(article["title"]): ("article.html", article)
        for article in data["news"]
    })

    pages.update({
        urls["example"] % slugify(example["title"]): ("example.html", example)
        for example in data["examples"]
    })

    shutil.rmtree("build", ignore_errors=True)
    os.mkdir("build")

    env = jinja2.Environment(loader=FileSystemLoader(["templates", "generated_templates"]), autoescape=True)
    env.filters["md"] = markdown.markdown
    env.filters["slugify"] = slugify

    for path, (template, extra_data) in pages.items():
        print("Rendering {}...".format(path))
        assert path[0] == "/"
        path = path[1:]
        *dir, file = path.split("/")
        file = file or "index.html"
        dir = "/".join(["build"] + dir)
        path = dir + "/" + file

        os.makedirs(dir, exist_ok=True)

        data = data.copy()
        data.update(extra_data)

        with open(path, "w") as f:
            env.get_template(template).stream(data).dump(f)

    shutil.copytree("static", "build", dirs_exist_ok=True)


if __name__ == "__main__":
    build()
