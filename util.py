import os.path
import re
import subprocess
import sys
from collections import OrderedDict
from contextlib import contextmanager

import requests
import toml
import os

ALT_VERCORS = os.environ.get("VERCORS_GIT", "")
VERCORS = ALT_VERCORS if ALT_VERCORS else "https://github.com/utwente-fmt/vercors.git"
print(VERCORS)

def load_data(path):
    with open("data/" + path + ".toml") as f:
        return toml.load(f, _dict=OrderedDict)


def slugify(text):
    return re.sub("[^a-z0-9]+", "-", text.lower()).strip("-")


def by_date_desc(xs):
    return list(reversed(sorted(xs, key=lambda x: x["date"])))


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
    local_vercors = None
    if os.path.isdir(VERCORS):
        local_vercors = VERCORS
    elif VERCORS.startswith("file://") and os.path.isdir(VERCORS[len("file://"):]):
        local_vercors = VERCORS[len("file://"):]

    mdbook_src = os.path.abspath(os.path.join("wiki_book", "src"))

    if local_vercors is not None:
        print("Using local vercors repository: {}".format(local_vercors))
        vercors_dir = local_vercors
        subprocess.run([
            sys.executable,
            os.path.join("util", "wiki", "generate_wiki_pdf.py"),
            "--mdbook", mdbook_src,
        ], cwd=vercors_dir, check=True)
        subprocess.run(["mdbook", "build"], cwd="wiki_book", check=True)
        return

    with clone(VERCORS, vercors_release_tag) as dir:
        vercors_dir = os.path.join(dir, "vercors")
        subprocess.run([
            sys.executable,
            os.path.join("util", "wiki", "generate_wiki_pdf.py"),
            "--mdbook", mdbook_src,
        ], cwd=vercors_dir, check=True)
        subprocess.run(["mdbook", "build"], cwd="wiki_book", check=True)