import os
import re
import shutil
import sys

import jinja2
import markdown
from jinja2 import FileSystemLoader
from datetime import datetime

from util import *


def render_wiki_fragments(urls, data):
    env = jinja2.Environment(loader=FileSystemLoader(["templates", "generated_templates"]), autoescape=True)
    env.filters["md"] = markdown.markdown
    env.filters["slugify"] = slugify
    return {
        "head": env.get_template("wiki_head.html").render(urls=urls, year=data["year"]),
        "header": env.get_template("header_wiki.html").render(urls=urls, year=data["year"]),
        "footer": env.get_template("footer.html").render(urls=urls, year=data["year"]),
    }


def postprocess_wiki_html(wiki_root, urls, data):
    fragments = render_wiki_fragments(urls, data)

    for dirpath, _, filenames in os.walk(wiki_root):
        for filename in filenames:
            if not filename.endswith(".html"):
                continue
            path = os.path.join(dirpath, filename)
            with open(path, "r", encoding="utf-8") as f:
                text = f.read()

            if "<head>" not in text or "</head>" not in text or "<body" not in text or "</body>" not in text:
                continue

            text = re.sub(
                r"(<head>)(.*?)(</head>)",
                lambda m: f"{m.group(1)}\n{fragments['head']}\n{m.group(2)}{m.group(3)}",
                text,
                flags=re.S,
                count=1,
            )

            if filename != "print.html":
                text = re.sub(
                    r"(<body[^>]*>)(.*?)",
                    lambda m: f"{m.group(1)}\n{fragments['header']}\n{m.group(2)}",
                    text,
                    flags=re.S,
                    count=1,
                )

                parts = text.rsplit("</body>", 1)
                if len(parts) == 2:
                    text = parts[0] + fragments["footer"] + "\n</body>" + parts[1]

            with open(path, "w", encoding="utf-8") as f:
                f.write(text)


def build():
    os.chdir(os.path.dirname(os.path.abspath(__file__)))

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
        "year": datetime.now().year,
    }

    print("Rendering bibliographies...")
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
        urls["external_papers"]: ("external_publications.html", {}),
        urls["news"]: ("news.html", {}),
        urls["examples"]: ("showcases.html", {}),
        urls["try_online"]: ("try_online.html", {}),
        urls["wiki"]: ("wiki/index.html", {}),
        urls["alpinist"]: ("alpinist.html", {}),
        urls["vesuv"]: ("vesuv.html", {}),
        urls["veymont"]: ("veymont.html", {}),
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

    local_wiki_book = os.path.join("wiki_book", "book")
    if os.path.isdir(local_wiki_book):
        print("Copying local wiki_book build output into build/wiki...")
        shutil.copytree(local_wiki_book, os.path.join("build", "wiki"), dirs_exist_ok=True)
    else:
        mdbook_book = os.path.join("generated_mdbook", "book")
        if os.path.isdir(mdbook_book):
            print("Copying generated mdBook output into build/wiki...")
            shutil.copytree(mdbook_book, os.path.join("build", "wiki"), dirs_exist_ok=True)

    print("Post-processing wiki HTML with Jinja fragments...")
    postprocess_wiki_html(os.path.join("build", "wiki"), urls, data)

    env = jinja2.Environment(loader=FileSystemLoader(["templates", "generated_templates"]), autoescape=True)
    env.filters["md"] = markdown.markdown
    env.filters["slugify"] = slugify

    for path, (template, extra_data) in pages.items():
        if path == "/wiki/":
            # The wiki is rendered by mdBook, so we don't need to render it here.
            continue
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
