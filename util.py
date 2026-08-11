import os
import pathlib
import re
import subprocess
from collections import OrderedDict
from collections.abc import Generator, Mapping, Sequence
from contextlib import contextmanager
from typing import Literal, TypedDict, TypeVar, cast, overload

import requests
import toml

from generate_wiki import generate_wiki


class ExampleSourceData(TypedDict):
    path: str
    description: str
    language: str
    backend: str
    verifies: bool
    lines_of_code: int
    lines_of_spec: int
    computation_time_ms: int
    features: list[str]


class ExampleData(ExampleSourceData):
    title: str


class ExampleDataWithContent(ExampleData, total=False):
    data: str


class NewsData(TypedDict):
    title: str
    date: str
    content: str


class LoadedExample(TypedDict):
    title: str
    path: str
    language: str
    backend: str
    verifies: bool
    lines_of_code: int
    lines_of_spec: int
    computation_time_ms: int
    description: str
    features: list[str]


class DatedData(TypedDict):
    date: str


class UrlsData(TypedDict):
    index: str
    about: str
    license: str
    news: str
    article: str
    publications: str
    external_papers: str
    try_online: str
    examples: str
    example: str
    wiki: str
    alpinist: str
    vesuv: str
    veymont: str
    external: dict[str, str]


class WebsiteData(TypedDict):
    urls: UrlsData
    about: Mapping[str, object]
    news: Sequence[NewsData]
    examples: Sequence[ExampleData]
    languages: Mapping[str, Mapping[str, str]]
    year: int
    references_html: str
    external_references_html: str

ALT_VERCORS: str = os.environ.get("VERCORS_GIT", "")
VERCORS: str = ALT_VERCORS if ALT_VERCORS else "https://github.com/utwente-fmt/vercors.git"
print(VERCORS)

ALT_VERCORS_WIKI: str = os.environ.get("VERCORS_WIKI_GIT", "")
VERCORS_WIKI: str = ALT_VERCORS_WIKI if ALT_VERCORS_WIKI else "https://github.com/utwente-fmt/vercors.wiki"

BASE_DIR: pathlib.Path = pathlib.Path(__file__).resolve().parent
DATA_DIR: pathlib.Path = BASE_DIR / "data"
WIKI_BOOK_DIR: pathlib.Path = BASE_DIR / "wiki_book"


@overload
def load_data(path: Literal["urls"]) -> UrlsData: ...


@overload
def load_data(path: Literal["about"]) -> Mapping[str, object]: ...


@overload
def load_data(path: Literal["news"]) -> Mapping[str, NewsData]: ...


@overload
def load_data(path: Literal["examples"]) -> Mapping[str, ExampleSourceData]: ...


@overload
def load_data(path: Literal["languages"]) -> Mapping[str, Mapping[str, str]]: ...


@overload
def load_data(path: str) -> object: ...


def load_data(path: str) -> object:
    with (DATA_DIR / f"{path}.toml").open() as f:
        return cast(object, toml.load(f, _dict=OrderedDict))


def slugify(text: str) -> str:
    return re.sub("[^a-z0-9]+", "-", text.lower()).strip("-")


TDate = TypeVar("TDate", bound=Mapping[str, object])


def by_date_desc(xs: Sequence[TDate]) -> list[TDate]:
    return sorted(xs, key=lambda x: str(x["date"]), reverse=True)


@overload
def titled(xs: Mapping[str, NewsData]) -> Sequence[NewsData]: ...


@overload
def titled(xs: Mapping[str, ExampleSourceData]) -> Sequence[ExampleData]: ...


@overload
def titled(xs: Mapping[str, Mapping[str, object]]) -> Sequence[Mapping[str, object]]: ...


def titled(xs: Mapping[str, Mapping[str, object]]) -> Sequence[Mapping[str, object]]:
    return [{"title": title, **data} for title, data in xs.items()]


def _cache_key(text: str) -> str:
    return re.sub("[^a-zA-Z0-9._-]+", "-", text).strip("-")


@contextmanager
def clone(url: str, tag: str) -> Generator[pathlib.Path, None, None]:
    cache_root: pathlib.Path = BASE_DIR / ".cache" / "git"
    cache_dir: pathlib.Path = cache_root / _cache_key(url) / _cache_key(tag)

    if not cache_dir.is_dir():
        cache_dir.mkdir(parents=True, exist_ok=True)
        subprocess.run(
            ["git", "clone", "--depth=1", "--branch", tag, url, str(cache_dir / "vercors")],
            check=True,
        )

    yield cache_dir


def fetch_bibliography_into(data: WebsiteData) -> None:
    data["references_html"] = requests.get(
        "https://bibbase.org/show?bib=https://raw.githubusercontent.com/utwente-fmt/vercors-web/master/static/references.bib&nocache=1").text
    data["external_references_html"] = requests.get(
        "https://bibbase.org/show?bib=https://raw.githubusercontent.com/utwente-fmt/vercors-web/master/static/external_papers.bib&nocache=1").text


def fetch_examples_into(data: WebsiteData, vercors_release_tag: str) -> None:
    with clone(VERCORS, vercors_release_tag) as dir:
        for example in data["examples"]:
            example_with_content = cast(ExampleDataWithContent, example)
            path = example_with_content["path"]

            example_path = dir / "vercors" / "examples" / path
            try:
                with example_path.open() as f:
                    example_with_content["data"] = f.read()
            except FileNotFoundError:
                print(f"[warning] File not found: {path}")
                example_with_content["data"] = ""


def fetch_wiki(vercors_release_tag: str) -> None:
    print("Creating wiki_book sources for mdbook...")
    mdbook_src: pathlib.Path = (WIKI_BOOK_DIR / "src").resolve()

    wiki_source = None
    if VERCORS_WIKI.startswith("file://"):
        local_wiki_path: pathlib.Path = pathlib.Path(VERCORS_WIKI[len("file://"):]).expanduser()
        if local_wiki_path.is_dir():
            wiki_source = str(local_wiki_path)
    elif pathlib.Path(VERCORS_WIKI).expanduser().is_dir():
        wiki_source = str(pathlib.Path(VERCORS_WIKI).expanduser())

    generate_wiki(str(mdbook_src), wiki_source)
    subprocess.run(["mdbook", "build"], cwd=str(WIKI_BOOK_DIR), check=True)