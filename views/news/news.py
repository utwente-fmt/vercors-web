import os

DIR = "/home/pieter/vercors-online/site/news/"

def escape(s):
    s = s.replace("\\", "\\\\")
    s = s.replace("\n", "\\n")
    s = s.replace("\"", "\\\"")
    return s

for name in os.listdir(DIR):
    f = DIR + name
    y, m, d, *_ = name.split("-")
    date = "{:04d}-{:02d}-{:02d}".format(int(y), int(m), int(d))
    lines = open(f).readlines()
    assert lines[0].strip() == "---"
    lines = lines[1:]
    title = None

    while lines[0].strip() != "---":
        try:
            attr, val = lines[0].strip().split(": ", 1)
            if attr == "title":
                title = val.strip()
            elif attr not in {"layout", "external_url"}:
                print(attr, val)
            lines = lines[1:]
        except:
            print(name, lines[0])
            raise

    lines = lines[1:]

    lines = [line.strip() for line in lines if line.strip()]
    content = "\n".join(lines)

    print(
"""INSERT INTO News (id, date, title, content) VALUES (NULL, "{}", "{}", "{}");""".format(date, escape(title), escape(content))
    )