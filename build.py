import os
import pathlib
import re
import shutil
from collections.abc import Mapping
from datetime import datetime, timezone
from typing import Protocol, TextIO, cast

import jinja2
import markdown
from jinja2 import FileSystemLoader

from util import *


class TemplateStream(Protocol):
    def dump(self, fp: TextIO) -> None: ...


def render_wiki_fragments(urls: UrlsData, data: WebsiteData) -> dict[str, str]:
    env = jinja2.Environment(loader=FileSystemLoader(["templates", "generated_templates"]), autoescape=True)
    env.filters["md"] = markdown.markdown  # pyright: ignore[reportUnknownMemberType]
    env.filters["slugify"] = slugify  # pyright: ignore[reportUnknownMemberType]
    return {
        "head": env.get_template("wiki_head.html").render(urls=urls, year=data["year"]),
        "header": env.get_template("header_wiki.html").render(urls=urls, year=data["year"]),
        "footer": env.get_template("footer.html").render(urls=urls, year=data["year"]),
    }


def postprocess_wiki_html(wiki_root: str | pathlib.Path, urls: UrlsData, data: WebsiteData) -> None:
    fragments: dict[str, str] = render_wiki_fragments(urls, data)

    for dirpath, _, filenames in os.walk(wiki_root):
        for filename in filenames:
            if not filename.endswith(".html"):
                continue
            path = pathlib.Path(dirpath) / filename
            with path.open("r", encoding="utf-8") as f:
                text = f.read()

            if "<head>" not in text or "</head>" not in text or "<body" not in text or "</body>" not in text:
                continue

            text: str = re.sub(
                r"(<head>)(.*?)(</head>)",
                lambda m: f"{m.group(1)}\n{fragments['head']}\n{m.group(2)}{m.group(3)}",
                text,
                flags=re.DOTALL,
                count=1,
            )

            if filename != "print.html":
                text: str = re.sub(
                    r"(<body[^>]*>)(.*?)",
                    lambda m: f"{m.group(1)}\n{fragments['header']}\n{m.group(2)}",
                    text,
                    flags=re.DOTALL,
                    count=1,
                )

                parts: list[str] = text.rsplit("</body>", 1)
                if len(parts) == 2:
                    text: str = parts[0] + fragments["footer"] + "\n</body>" + parts[1]

            with path.open("w", encoding="utf-8") as f:
                f.write(text)


def build() -> None:
    base_dir: pathlib.Path = pathlib.Path(__file__).resolve().parent
    os.chdir(base_dir)

    shutil.rmtree(base_dir / "generated_templates", ignore_errors=True)
    (base_dir / "generated_templates").mkdir(exist_ok=True)

    print("Loading the urls...")
    urls = load_data("urls")

    print("Loading other data...")
    data: WebsiteData = {
        "urls": urls,
        "about": load_data("about"),
        "news": by_date_desc(titled(load_data("news"))),
        "examples": titled(load_data("examples")),
        "languages": load_data("languages"),
        "year": datetime.now(tz=timezone.utc).year,
        "references_html": "",
        "external_references_html": "",
    }

    print("Rendering bibliographies...")
    fetch_bibliography_into(data)

    print("Fetching example data from git repository...")
    fetch_examples_into(data, "v1.4.0")

    print("Rendering wiki...")
    fetch_wiki("dev")

    print("Computing routes and template data...")
    pages: dict[str, tuple[str, Mapping[str, object]]] = {
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

    shutil.rmtree(base_dir / "build", ignore_errors=True)
    (base_dir / "build").mkdir(exist_ok=True)

    local_wiki_book: pathlib.Path = base_dir / "wiki_book" / "book"
    print("Copying local wiki_book build output into build/wiki...")
    shutil.copytree(local_wiki_book, base_dir / "build" / "wiki", dirs_exist_ok=True)

    print("Post-processing wiki HTML with Jinja fragments...")
    postprocess_wiki_html(base_dir / "build" / "wiki", urls, data)

    env = jinja2.Environment(loader=FileSystemLoader(["templates", "generated_templates"]), autoescape=True)
    env.filters["md"] = markdown.markdown  # pyright: ignore[reportUnknownMemberType]
    env.filters["slugify"] = slugify  # pyright: ignore[reportUnknownMemberType]

    for path, (template, extra_data) in pages.items():
        if path == "/wiki":
            # The wiki is rendered by mdBook, so we don't need to render it here.
            continue
        print(f"Rendering {path}...")
        assert path[0] == "/"
        path = path[1:]
        *dir_parts, file = path.split("/")
        file = file or "index.html"
        build_dir: pathlib.Path = pathlib.Path("build") / pathlib.Path(*dir_parts)
        path = build_dir / file

        build_dir.mkdir(parents=True, exist_ok=True)

        page_data: dict[str, object] = dict(data)
        page_data.update(extra_data)

        with path.open("w") as f:
            stream: TemplateStream = cast(TemplateStream, env.get_template(template).stream(page_data))
            stream.dump(f)

    shutil.copytree(base_dir / "static", base_dir / "build", dirs_exist_ok=True)


if __name__ == "__main__":
    build()
