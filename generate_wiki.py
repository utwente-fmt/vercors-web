import base64
import json
import optparse
import os
import pathlib
import re
import shutil
import subprocess
import sys
import tempfile
from html import escape
from typing import Literal, TypedDict, cast
from urllib.parse import unquote

import pypandoc


class SidebarNode(TypedDict):
    kind: Literal["chapter", "group", "page"]
    title: str
    children: list["SidebarNode"]
    file_name: str | None


Sidebar = dict[str, list[SidebarNode]]


def ensure_pandoc_available() -> None:
    """
    Ensure pypandoc resolves a usable pandoc binary.
    """
    try:
        version: str = pypandoc.get_pandoc_version()
    except OSError:
        print("Pandoc was not found for pypandoc.")
        print("Install pandoc manually (e.g. 'sudo apt install pandoc' "
        + "or in a python terminal with 'pypandoc.download_pandoc()')", file=sys.stderr)
        sys.exit(2)
    print(f"Using pandoc via pypandoc: {version}")


class SnippetTestcase:
    """
    A testcase consisting of custom snippets, e.g.:

    <!-- standaloneSnip smallCase
    //:: cases smallCase
    //:: verdict Fail
    class Test {
    void test() {
    -->

    This example will **fail**:

    <!-- codeSnip smallCase -->
    ```java
    assert false;
    ```

    <!-- standaloneSnip smallCase
    }
    }
    -->
    """

    def __init__(self) -> None:
        self.content: str = ""
        self.language: str = ""

    def add_content(self, content: str) -> None:
        self.content += content

    def render(self) -> str:
        return self.content

class UnknownVerdict(Exception):
    pass

class TemplateTestcase:
    """
    Testcases defined by template, e.g.:

    <!-- testBlock Fail -->
    ```java
    assert false;
    ```

    testBlock wraps the code in a method and class
    testMethod wraps the code in a class
    test returns the code as is

    testBlock and testMethod are compatible with java and pvl.
    The case name is derived from the heading structure.
    """

    METHOD = \
"""{final}class Test {{
{content}
}}"""

    BLOCK = \
"""{final}class Test {{
    void test() {{
{content}
    }}
}}"""

    HEADER = \
"""//:: cases {case_name}
//:: verdict {verdict}
//:: tools silicon
"""

    def __init__(self, case_name: str, template_kind: str, verdict: str) -> None:
        if verdict and not verdict in {"Pass", "Fail", "Error", "PassOnLatest", "FailOnLatest"}:
                raise UnknownVerdict()

        self.template_kind: str = template_kind
        self.case_name: str = case_name
        self.verdict: str = verdict if verdict else "Pass"
        self.content: str | None = None
        self.language: str = ""

    def add_content(self, content: str) -> None:
        if self.content is not None:
            raise RuntimeError

        self.content = content

    def indent(self, amount: int, text: str) -> str:
        return '\n'.join("    " * amount + line for line in text.split("\n"))
    
    def render_header(self) -> str:
        return TemplateTestcase.HEADER.format(case_name=self.case_name, verdict=self.verdict)

    def render_body(self) -> str:
        content = self.content
        if content is None:
            raise RuntimeError("template testcase has no content")

        if self.template_kind == 'test':
            return content
        elif self.template_kind == 'testMethod':
            return TemplateTestcase.METHOD.format(
                    final="final " if self.language == "java" else "",
                    content=self.indent(1, content)
                    )
        elif self.template_kind == 'testBlock':
            return TemplateTestcase.BLOCK.format(
                    final="final " if self.language == "java" else "",
                    content=self.indent(2, content)
                    )
        else:
            raise RuntimeError()

    def render(self) -> str:
        return self.render_header() + self.render_body()

def slugify(text: str) -> str:
    slug = re.sub(r"[^a-z0-9]+", "-", text.lower()).strip("-")
    return slug or "section"

def new_sidebar_node(
    kind: Literal["chapter", "group", "page"],
    title: str,
    children: list[SidebarNode] | None = None,
    file_name: str | None = None,
) -> SidebarNode:
    node: SidebarNode = {
        'kind': kind,
        'title': title,
        'children': [] if children is None else children,
        'file_name': file_name,
    }
    return node

def parse_sidebar(contents: str) -> Sidebar:
    chapter_heading_re = re.compile(r"^\*\*(.+?)\*\*\s*$")
    list_item_re = re.compile(r"^(?P<indent>\s*)\*\s+(?P<body>.+?)\s*$")
    any_re = re.compile(r"\[(.+?)\]\(https.*\/(.+?)\)")
    chapter_re = re.compile(r"\[([^\]]+)\]\(https.*\/([^)]+)\)")

    chapters: list[SidebarNode] = []
    pages: list[SidebarNode] = []
    current_chapter: SidebarNode | None = None
    stack: list[tuple[int, SidebarNode]] = []

    for raw_line in contents.splitlines():
        line = raw_line.rstrip()
        stripped = line.strip()
        if not stripped:
            continue

        chapter_heading_match = chapter_heading_re.match(stripped)
        if chapter_heading_match:
            heading_title: str = chapter_heading_match.group(1)
            heading_link_match = chapter_re.fullmatch(heading_title)
            if heading_link_match:
                link_title, _ = heading_link_match.groups()
                if link_title == 'Home':
                    current_chapter = None
                    stack = []
                    continue
                heading_title = link_title

            current_chapter = new_sidebar_node('chapter', heading_title)
            chapters.append(current_chapter)
            stack = [(-1, current_chapter)]
            continue

        list_item_match = list_item_re.match(line)
        if not list_item_match:
            if any_re.search(line):
                print(f"Warning: sidebar entry did not match chapter_re and is not included: {stripped}", file=sys.stderr)
            continue

        if current_chapter is None:
            continue

        indent: int = len(list_item_match.group('indent').replace('\t', '  '))
        body: str = list_item_match.group('body').strip()
        link_match = chapter_re.search(body)

        if link_match:
            title, file_name = link_match.groups()
            node = new_sidebar_node('page', title, file_name=file_name)
        else:
            if any_re.search(body):
                print(f"Warning: sidebar entry did not match chapter_re and is not included: {body}", file=sys.stderr)
                continue
            node = new_sidebar_node('group', body)

        while stack and indent <= stack[-1][0]:
            stack.pop()

        parent = stack[-1][1]
        parent['children'].append(node)
        stack.append((indent, node))

        if node['kind'] == 'page' and node['title'] != 'Home':
            pages.append(node)

    return {
        'chapters': chapters,
        'pages': pages,
    }

def load_sidebar(wiki_location: str) -> Sidebar:
    with pathlib.Path(wiki_location, "_Sidebar.md").open("r") as f:
        contents: str = f.read()

    contents = unquote(contents)
    sidebar = parse_sidebar(contents)
    return sidebar


def generate_wiki(mdbook_path: str | pathlib.Path, wiki_location: str | None) -> None:
    ensure_pandoc_available()
    if wiki_location is None:
        path = tempfile.mkdtemp()
        subprocess.run(["git", "clone", "https://github.com/utwente-fmt/vercors.wiki.git"], cwd=path, check=True)
        wiki_location = os.path.join(path, "vercors.wiki")

    sidebar = load_sidebar(wiki_location)
    output_mdbook(mdbook_path, wiki_location, sidebar)

def collect_testcases(document: dict[str, object], cases: dict[str, SnippetTestcase | TemplateTestcase]) -> None:
    """
    Walks through the blocks of the document and collects test cases as described in SnippetTestcase and TemplateTestcase
    """
    breadcrumbs: list[str] = []
    testcase_number: int = 1
    code_block_label: str | None = None

    blocks = cast(list[dict[str, object]], document['blocks'])
    for block in blocks:
        # Code blocks preceded by a label are added to the labeled testcase
        block_type: str = cast(str, block['t'])
        if block_type == 'CodeBlock' and code_block_label is not None:
            code_block_data: list[object] = cast(list[object], block['c'])
            code_txt: str = cast(str, code_block_data[1])
            cases[code_block_label].add_content(code_txt)

            languages: list[str] = cast(list[str], cast(list[object], code_block_data[0])[1])
            if len(languages) == 0:
                print(f"Error: language was not specified for code block.\nLabel: {code_block_label}\nText in code block:\n{code_txt}")
                sys.exit(1)

            cases[code_block_label].language = languages[0]
            block['_case_label'] = code_block_label

        code_block_label = None

        # Headers are put into the breadcrumbs for template testcases
        if block_type == 'Header':
            # if the breadcrumbs are [Heading, Section, Subsection]
            # and we have a new section "Section 2"
            # the breadcrumbs should be [Heading, Section 2]
            header_data: list[object] = cast(list[object], block['c'])
            header_level: int = cast(int, header_data[0])
            breadcrumbs = breadcrumbs[:header_level]
            breadcrumbs += ['wiki'] * (header_level - len(breadcrumbs))
            header_id: str = cast(str, cast(list[object], header_data[1])[0])
            if not header_id:
                header_text: list[str] = []
                for element in cast(list[dict[str, object]], header_data[2]):
                    element_type: str = cast(str, element['t'])
                    if element_type == 'Str':
                        header_text.append(cast(str, element['c']))
                    elif element_type == 'Space':
                        header_text.append(' ')
                    elif element_type == 'Code':
                        header_text.append(cast(str, cast(list[object], element['c'])[1]))
                header_id = slugify(''.join(header_text)) if header_text else 'section'
            breadcrumbs[header_level - 1] = header_id
            testcase_number = 1

        # Raw blocks that are comments starting with something we recognize are processed
        if block_type == 'RawBlock':
            raw_block_data: list[object] = cast(list[object], block['c'])
            if cast(str, raw_block_data[0]) != 'html':
                continue

            content: str = cast(str, raw_block_data[1]).strip()
            if content.startswith('<!--') and content.endswith('-->'):
                lines: list[str] = [line.strip() for line in content[4:-3].strip().split('\n')]
                kind, *args = lines[0].split(' ')

                # Template label
                if kind in {'testBlock', 'testMethod', 'test'}:
                    base_label = '-'.join(breadcrumbs) if breadcrumbs else 'wiki'
                    code_block_label = base_label + '-' + str(testcase_number)
                    testcase_number += 1
                    cases[code_block_label] = TemplateTestcase(code_block_label, kind, args[0] if args else 'Pass')

                # Snippet
                if kind == 'standaloneSnip':
                    label_prefix = breadcrumbs[0] if breadcrumbs else 'wiki'
                    label = label_prefix + '-' + args[0]

                    if label not in cases:
                        cases[label] = SnippetTestcase()
                    else:
                        cases[label].add_content("\n")

                    cases[label].add_content('\n'.join(lines[1:]) + '\n')

                # Snippet label for code block
                if kind == 'codeSnip':
                    label_prefix = breadcrumbs[0] if breadcrumbs else 'wiki'
                    code_block_label = label_prefix + '-' + args[0]

                    if code_block_label not in cases:
                        cases[code_block_label] = SnippetTestcase()

def get_html(elements: list[dict[str, object]]) -> str:
    result: str = ""

    for element in elements:
        element_type: str = cast(str, element['t'])
        if element_type == 'Str':
            result += cast(str, element['c'])
        elif element_type == 'Space':
            result += ' '
        elif element_type == 'SoftBreak':
                    result += '\n'
        elif element_type == 'Code':
            result += '<code>' + cast(str, cast(list[object], element['c'])[1]) + '</code>'
        elif element_type == 'RawInline':
            raw_inline = cast(list[object], element['c'])
            if cast(str, raw_inline[0]) == 'html':
                result += cast(str, raw_inline[1])
            else:
                assert False, f"Unrecognized element type for HTML header: {element_type} in block {element}"
        else:
            assert False, f"Unrecognized element type for HTML header: {element_type} in block {element}"

    return result

class UnknownLanguageError(Exception):
    pass

class CasesExtractionFailed(Exception):
    pass

def is_known_language(language: str) -> bool:
    return language.lower() in {"java", "c", "opencl", "pvl", "cu", "cuda", "cpp", "c++", "sycl", "viper"}

def language_to_extension(language: str) -> str:
    # Ok, this looks a bit stupid, but we cannot assume the "language" attribute github uses for markdown code snippets will never diverge from extensions used for files of that type...
    language = language.lower()
    if language == "java":
        return "java"
    if language == "opencl":
        return "cl"
    elif language == "c":
        return "c"
    elif language == "pvl":
        return "pvl"
    elif language == "cuda" or language == "cu":
        return "cu"
    elif language == "cpp" or language == "c++" or language == "sycl":
        return "cpp"
    elif language == "viper": 
        return "vpr"
    else:
        raise UnknownLanguageError(language)

def output_cases(path: str, cases: dict[str, SnippetTestcase | TemplateTestcase]) -> None:
    os.makedirs(path, exist_ok=True)

    ok = 0
    not_ok = 0

    for case_name, case in cases.items():
        try:
            p: str = os.path.join(path, f"{case_name}.{language_to_extension(case.language)}")
            content: str = case.render()
            with open(p, "w") as f:
                f.write(content)
            ok += 1
        except UnknownLanguageError:
            print(f"Unknown language {case.language} in case {case_name}")
            not_ok += 1

    print(f"Extracted {ok} cases successfully. {not_ok} cases failed.")

    if not_ok > 0:
        raise CasesExtractionFailed
    
def render_verification_editor_html(initial_code: str, initial_hidden_code: str, language_extension: str, language_label: str, template_kind: str | None = None, case_name: str | None = None, verdict: str = 'Pass') -> str:
    def html_text_no_markdown_breaks(text: str) -> str:
        # Keep raw HTML blocks stable in mdBook markdown by avoiding literal blank lines.
        return escape(text).replace('\r\n', '\n').replace('\r', '\n').replace('\n', '&#10;')

    hidden_code = html_text_no_markdown_breaks(initial_hidden_code)
    full_code_base64 = base64.b64encode(initial_code.encode('utf-8')).decode('ascii')
    template_kind_attr = escape(template_kind or '', quote=True)
    case_name_attr = escape(case_name or '', quote=True)
    verdict_attr = escape(verdict or 'Pass', quote=True)
    language_extension_attr = escape(language_extension, quote=True)
    language_label_attr = escape(language_label, quote=True)
    code_snippet = " code_snippet" if not template_kind else ""

    return f"""<div class="verification-container" data-examplecode-b64="{full_code_base64}" data-template-kind="{template_kind_attr}" data-case-name="{case_name_attr}" data-case-verdict="{verdict_attr}" data-language-ext="{language_extension_attr}" data-language-label="{language_label_attr}"><pre style="margin-bottom: 0" class="verification-text playground"><code class="language-{escape(language_extension)} no_run editable{code_snippet}">{hidden_code}</code></pre><div class="verification-language">Language for VerCors: <strong>{escape(language_label)}</strong></div><div class="verification-progress verification-non-plain" style="display: none;"><span class="fa"></span><span class="verification-progress-text"></span></div><pre class="verification-log verification-non-plain" style="display: none"></pre></div>"""


def render_onlatest_warning_markdown() -> str:
    return (
        "> [!WARNING]\n"
        "> The following example verifies only on the latest [VerCors development version](https://github.com/utwente-fmt/vercors/tree/dev), "
        "not on the version currently used by the online verifier."
    )

def convert_block_mdbook(block: dict[str, object], cases: dict[str, SnippetTestcase | TemplateTestcase], source_name: str, current_header: str | None) -> list[dict[str, object]]:
    block_type: str = cast(str, block['t'])
    if block_type == 'CodeBlock' and '_case_label' in block:
        case: SnippetTestcase | TemplateTestcase = cases[cast(str, block['_case_label'])]
        try:
            language_extension = language_to_extension(case.language)
        except UnknownLanguageError: 
            print(f"Warning: {source_name} / {current_header}: code block has unknown language '{case.language}'.", file=sys.stderr)
            sys.exit(1)
            # return block
        template_kind: str | None = case.template_kind if isinstance(case, TemplateTestcase) else None
        case_name: str | None = case.case_name if isinstance(case, TemplateTestcase) else None
        verdict: str = case.verdict if isinstance(case, TemplateTestcase) else 'Pass'
        language_label: str = case.language or language_extension
        converted_blocks: list[dict[str, object]] = []
        if verdict.endswith('OnLatest'):
            converted_blocks.append({
                't': 'RawBlock',
                'c': ['markdown', render_onlatest_warning_markdown()],
            })
        code_block_data: list[object] = cast(list[object], block['c'])
        converted_blocks.append({
            't': 'RawBlock',
            'c': ['html', render_verification_editor_html(case.render(), cast(str, code_block_data[1]), language_extension, language_label, template_kind, case_name, verdict)],
        })
        return converted_blocks
    if block_type == 'CodeBlock':
        code_block_data: list[object] = cast(list[object], block['c'])
        classes: list[str] = cast(list[str], cast(list[object], code_block_data[0])[1])
        if len(classes) == 0:
            header_text = current_header if current_header else 'document start'
            print(f"Warning: {source_name} / {header_text}: code block has no language specified.", file=sys.stderr)
        if all(not is_known_language(c) for c in classes):
            return [block]
        info_string: str = ','.join(classes)
        new_classes: list[str] = classes.copy()
        if not 'editable' in classes:
            new_classes.append('editable')
        if not 'read-only' in classes:
            new_classes.append('read-only')

        info_string = ','.join(new_classes)
        code_text: str = cast(str, code_block_data[1])
        fence: str = '```'
        while fence in code_text:
            fence += '`'
        return [{
            't': 'RawBlock',
            'c': ['markdown', f"{fence}{info_string}\n{code_text}\n{fence}"]
        }]
    return [block]


def remove_proselint_ignore_lines(markdown_text: str) -> str:
    return re.sub(
        r"(?m)^[ \t]*<!-- proselint-ignore -->[ \t]*\r?\n?",
        "",
        markdown_text,
    )

def transform_markdown_for_mdbook(markdown_text: str, source_name: str, source_markdown_pages: set[str]) -> str:
    markdown_text = remove_proselint_ignore_lines(markdown_text)
    markdown_text = rewrite_vercors_wiki_links(markdown_text, source_name, source_markdown_pages)
    document: dict[str, object] = json.loads(pypandoc.convert_text(markdown_text, "json", "gfm"))
    cases: dict[str, SnippetTestcase | TemplateTestcase] = {}
    collect_testcases(document, cases)
    transformed_blocks: list[dict[str, object]] = []
    current_header: str | None = None

    for block in cast(list[dict[str, object]], document['blocks']):
        if cast(str, block['t']) == 'Header':
            current_header = get_html(cast(list[dict[str, object]], cast(list[object], block['c'])[2])).strip()
        transformed_blocks.extend(convert_block_mdbook(block, cases, source_name, current_header))

    transformed_document = json.dumps({
        'blocks': transformed_blocks,
        'pandoc-api-version': document['pandoc-api-version'],
        'meta': document['meta'],
    })
    converted: str = pypandoc.convert_text(
        transformed_document,
        "gfm",
        "json",
        extra_args=["--wrap=none"],
    )
    return unescape_blockquote_callouts(converted)

def render_mdbook_summary_nodes(nodes: list[SidebarNode], depth: int = 1) -> list[str]:
    lines: list[str] = []
    indent: str = '  ' * depth

    for node in nodes:
        node_kind: str = cast(str, node['kind'])
        if node_kind == 'chapter':
            lines.append(f"# {node['title']}")
            lines.extend(render_mdbook_summary_nodes(node['children'], depth=1))
        elif node_kind == 'group':
            lines.append(f"{indent}- [{node['title']}]()")
            lines.extend(render_mdbook_summary_nodes(node['children'], depth=depth + 1))
        else:
            file_name = node['file_name']
            if file_name is None:
                raise RuntimeError("page node is missing a file name")
            lines.append(f"{indent}- [{node['title']}]({file_name}.md)")
            lines.extend(render_mdbook_summary_nodes(node['children'], depth=depth + 1))

    return lines

def copy_mdbook_sources(source_path: str | pathlib.Path, book_root: str | pathlib.Path, sidebar: Sidebar) -> None:
    source_markdown_pages: set[str] = {
        os.path.splitext(name)[0]
        for name in os.listdir(source_path)
        if name.endswith('.md')
    }

    for name in os.listdir(source_path):
        if not name.endswith('.md') or name == '_Sidebar.md':
            continue
        source_file = os.path.join(source_path, name)
        with open(source_file, 'r') as f:
            transformed_markdown = transform_markdown_for_mdbook(f.read(), name, source_markdown_pages)
        with open(os.path.join(book_root, name), 'w') as f:
            f.write(transformed_markdown)

    summary_lines = ['# Summary', '',
                        '']
    summary_lines.extend(render_mdbook_summary_nodes(sidebar['chapters']))
    summary_text = '\n'.join(summary_lines).rstrip() + '\n'

    with open(os.path.join(book_root, 'SUMMARY.md'), 'w') as f:
        f.write(summary_text)

def output_mdbook(path: str | pathlib.Path, source_path: str | pathlib.Path, sidebar: Sidebar) -> None:
    if os.path.isdir(path):
        shutil.rmtree(path)
    os.makedirs(path, exist_ok=True)
    copy_mdbook_sources(source_path, path, sidebar)


def rewrite_vercors_wiki_links(markdown_text: str, source_name: str, source_markdown_pages: set[str]) -> str:
    def replace_link(match: re.Match[str]) -> str:
        title: str = match.group(1)
        target: str = match.group(2)
        if not target:
            raise ValueError(
                "Malformed VerCors wiki link: missing target path in regex group 2. "
                f"Matched text: {match.group(0)!r}"
            )
        target = unquote(target)
        if target not in source_markdown_pages:
            raise ValueError(
                f"Broken VerCors wiki link in {source_name}: target markdown file '{target}.md' does not exist in source_path. "
                f"Matched text: {match.group(0)!r}"
            )
        if match.group(3):
            return f"[{title}]({target}.md{match.group(3)})"
        return f"[{title}]({target}.md)"

    return re.sub(
        r"\[([^\]]+)\]\((?:https://)?github\.com/utwente-fmt/vercors/wiki/([^)#]+?)(?:\.md)?(#.[^)]*)?\)",
        replace_link,
        markdown_text,
    )


def unescape_blockquote_callouts(markdown_text: str) -> str:
    # Pandoc may escape callout markers like > \[!WARNING\],
    # but mdBook expects > [!WARNING].
    markdown_text = re.sub(
        r"(^\s*>\s*)\\\[(![A-Za-z0-9_-]+)\\\]",
        r"\1[\2]",
        markdown_text,
        flags=re.MULTILINE,
    )
    markdown_text = re.sub(
        r"(^\s*>\s*)\\(\[![A-Za-z0-9_-]+\])",
        r"\1\2",
        markdown_text,
        flags=re.MULTILINE,
    )
    # Keep GitHub-style callout markers on their own quote line:
    # > [!NOTE]
    # > body text...
    return re.sub(
        r"(^\s*>\s*\[![A-Za-z0-9_-]+\])\s+([^\n].*)$",
        r"\1\n> \2",
        markdown_text,
        flags=re.MULTILINE,
    )

if __name__ == "__main__":
    parser = optparse.OptionParser()
    parser.add_option('-i', '--input', dest='source_path', help='directory where the wiki is stored', metavar='FILE')
    parser.add_option('-c', '--cases', dest='cases_path', help='write test cases extracted from the wiki to a folder')
    parser.add_option('--mdbook', dest='mdbook_path', help='write wiki to an mdBook project directory', metavar='FILE')


    options, args = parser.parse_args()

    if not any([options.cases_path, options.mdbook_path]):
        parser.error("No output type: please set one or more of the output paths. (try --help)")

    ensure_pandoc_available()

    if options.source_path:
        source_path = options.source_path
    else:
        path = tempfile.mkdtemp()
        subprocess.run(["git", "clone", "https://github.com/utwente-fmt/vercors.wiki.git"], cwd=path, check=True)
        source_path = os.path.join(path, "vercors.wiki")
    
    if options.cases_path:
        print("Creating wiki test suite...")
        cases: dict[str, SnippetTestcase | TemplateTestcase] = {}

        for name in os.listdir(source_path):
            if not name.endswith('.md') or name == '_Sidebar.md':
                continue
            source_file = os.path.join(source_path, name)
            with open(source_file, 'r') as f:
                markdown_text: str = f.read()
            document: dict[str, object] = json.loads(pypandoc.convert_text(markdown_text, "json", "gfm"))
            collect_testcases(document, cases)

        output_cases(options.cases_path, cases)
        print("done")
    if options.mdbook_path:
        print("Creating mdBook project...")
        sidebar = load_sidebar(source_path)
        output_mdbook(options.mdbook_path, source_path, sidebar)
