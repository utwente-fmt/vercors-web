import os.path
import re
import subprocess
import sys
import shutil
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
    # Prefer the local wiki_book sources if available.
    if os.path.isdir("wiki_book"):
        print("Building local wiki_book sources with mdbook...")
        subprocess.run(["mdbook", "build"], cwd="wiki_book", check=True)
        return

    local_vercors = None
    if os.path.isdir(VERCORS):
        local_vercors = VERCORS
    elif VERCORS.startswith("file://") and os.path.isdir(VERCORS[len("file://"):]):
        local_vercors = VERCORS[len("file://"):]

    if local_vercors is not None:
        wiki_util_path = os.path.join(local_vercors, "util", "wiki")
        mdbook_root = "generated_mdbook"
        shutil.rmtree(mdbook_root, ignore_errors=True)
        command = [
            sys.executable,
            os.path.join(wiki_util_path, "generate_wiki_pdf.py"),
            "--mdbook", mdbook_root,
        ]

        local_wiki_path = os.path.join(local_vercors, "vercors.wiki")
        if os.path.isdir(local_wiki_path):
            command.extend(["-i", local_wiki_path])

        subprocess.run(command, check=True)
        subprocess.run(["mdbook", "build", mdbook_root], check=True)
        return

    with clone(VERCORS, vercors_release_tag) as dir:
        wiki_util_path = dir + "/vercors/util/wiki/"
        subprocess.run(["virtualenv", "venv"], cwd=wiki_util_path)
        subprocess.run(["./venv/local/bin/pip", "install", "-r", "requirements.txt"], cwd=wiki_util_path)
        subprocess.run([
            wiki_util_path + "venv/local/bin/python",
            wiki_util_path + "generate_wiki_pdf.py",
            "--mdbook", "generated_mdbook"
        ], check=True)
        subprocess.run(["mdbook", "build", "generated_mdbook"], check=True)