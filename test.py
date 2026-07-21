import socketserver
import webbrowser
import os
from functools import partial
from http.server import SimpleHTTPRequestHandler

import build


os.chdir(os.path.dirname(os.path.abspath(__file__)))
print("Building the website...")
build.build()
print("Now serving on http://localhost:8000/")
socketserver.TCPServer.allow_reuse_address = True
Handler = partial(SimpleHTTPRequestHandler, directory="build")
socketserver.TCPServer(("0.0.0.0", 8000), Handler).serve_forever()