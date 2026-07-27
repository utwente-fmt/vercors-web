import os
import os.path
import re
import subprocess
import sys
from collections import OrderedDict
from contextlib import contextmanager

import requests
import toml

ALT_VERCORS = os.environ.get("VERCORS_GIT", "")
VERCORS = ALT_VERCORS if ALT_VERCORS else "https://github.com/utwente-fmt/vercors.git"
print(VERCORS)

ALT_VERCORS_WIKI = os.environ.get("VERCORS_WIKI_GIT", "")
VERCORS_WIKI = ALT_VERCORS_WIKI if ALT_VERCORS_WIKI else "https://github.com/utwente-fmt/vercors.wiki"

def load_data(path):
    with open("data/" + path + ".toml") as f:
        return toml.load(f, _dict=OrderedDict)


def slugify(text):
    return re.sub("[^a-z0-9]+", "-", text.lower()).strip("-")


def by_date_desc(xs):
    return sorted(xs, key=lambda x: x["date"], reverse=True)


def titled(xs):
    return [{"title": title, **data} for title, data in xs.items()]


def _cache_key(text):
    return re.sub("[^a-zA-Z0-9._-]+", "-", text).strip("-")


@contextmanager
def clone(url, tag):
    cache_root = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".cache", "git")
    cache_dir = os.path.join(cache_root, _cache_key(url), _cache_key(tag), )

    if not os.path.isdir(cache_dir):
        os.makedirs(cache_root, exist_ok=True)
        subprocess.run(
            ["git", "clone", "--depth=1", "--branch", tag, url, os.path.join(cache_dir, "vercors")],
            check=True,
        )

    yield cache_dir


def fetch_bibliography_into(data):
    data["references_html"] = requests.get(
        "https://bibbase.org/show?bib=https://raw.githubusercontent.com/utwente-fmt/vercors-web/master/static/references.bib&nocache=1").text
    data["external_references_html"] = requests.get(
        "https://bibbase.org/show?bib=https://raw.githubusercontent.com/utwente-fmt/vercors-web/master/static/external_papers.bib&nocache=1").text


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
    print("Creating wiki_book sources for mdbook...")
    mdbook_src = os.path.abspath(os.path.join("wiki_book", "src"))
    generate_wiki = os.path.join(os.path.dirname(os.path.abspath(__file__)), "generate_wiki.py")

    wiki_source = None
    if os.path.isdir(VERCORS_WIKI):
        wiki_source = VERCORS_WIKI
    elif VERCORS_WIKI.startswith("file://") and os.path.isdir(VERCORS_WIKI[len("file://"):]):
        wiki_source = VERCORS_WIKI[len("file://"):]

    run_args = [
        sys.executable,
        generate_wiki,
        "--mdbook", mdbook_src,
    ]
    if wiki_source is not None:
        print(f"Using local wiki repository: {wiki_source}")
        run_args[2:2] = ["--input", wiki_source]

    subprocess.run(run_args, check=True)
    subprocess.run(["mdbook", "build"], cwd="wiki_book", check=True)