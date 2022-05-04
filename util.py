import os.path
import re
import subprocess
import tempfile
from collections import OrderedDict

import requests
import toml


VERCORS = "https://github.com/utwente-fmt/vercors.git"


def load_data(path):
    with open("data/" + path + ".toml") as f:
        return toml.load(f, _dict=OrderedDict)


def slugify(text):
    return re.sub("[^a-z0-9]+", "-", text.lower()).strip("-")


def by_date_desc(xs):
    return list(reversed(sorted(xs, key=lambda x: x["date"])))


def titled(xs):
    return [{"title": title, **data} for title, data in xs.items()]


def clone(url, tag):
    dir = tempfile.TemporaryDirectory()
    subprocess.run(["git", "clone", "--depth=1", "--branch", tag, url], cwd=dir.name)
    return dir


def fetch_bibliography_into(data):
    data["references_html"] = requests.get(
        "https://bibbase.org/show?bib=https://raw.githubusercontent.com/utwente-fmt/vercors-web/master/static/references.bib").text


def fetch_examples_into(data, vercors_release_tag):
    with clone(VERCORS, vercors_release_tag) as dir:
        for example in data["examples"]:
            try:
                with open(dir + "/vercors/examples/" + example["path"]) as f:
                    example["data"] = f.read()
            except FileNotFoundError:
                print("[warning] File not found: {}".format(example["path"]))
                example["data"] = ""


def fetch_wiki(vercors_release_tag):
    with clone(VERCORS, vercors_release_tag) as dir:
        wiki_util_path = dir + "/vercors/util/wiki/"
        subprocess.run(["virtualenv", "venv"], cwd=wiki_util_path)
        subprocess.run(["./venv/bin/pip", "install", "-r", "requirements.txt"], cwd=wiki_util_path)
        subprocess.run([
            wiki_util_path + "venv/bin/python",
            wiki_util_path + "generate_wiki_pdf.py",
            "--jinja", "generated_templates/wiki_content.html",
            "--menu", "generated_templates/wiki_menu.html"
        ])