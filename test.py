import socketserver
import webbrowser
from http.server import SimpleHTTPRequestHandler

import build


class Handler(SimpleHTTPRequestHandler):
    def do_GET(self):
        self.path = "/build" + self.path
        return super(Handler, self).do_GET()


print("Building the website...")
build.build()
print("Now serving on http://localhost:8000/")
socketserver.TCPServer.allow_reuse_address = True
socketserver.TCPServer(("0.0.0.0", 8000), Handler).serve_forever()