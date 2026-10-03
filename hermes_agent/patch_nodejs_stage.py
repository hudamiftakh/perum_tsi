with open('/home/wabotweb/.hermes/hermes-agent/pm/packages.py', 'r') as f:
    text = f.read()

target = 'probe_version = False'
replacement = """probe_version = False

    def stage(self, store, staged, version, target):
        super().stage(store, staged, version, target)
        node_bin = staged / "bin/node"
        if node_bin.exists():
            node_bin.write_text('#!/bin/bash\\nexec /opt/cpanel/ea-nodejs22/bin/node "$@"\\n')
            node_bin.chmod(0o755)"""

if target in text and 'def stage(self, store, staged, version, target):' not in text:
    text = text.replace(target, replacement, 1)
    with open('/home/wabotweb/.hermes/hermes-agent/pm/packages.py', 'w') as f:
        f.write(text)
    print("PATCHED Nodejs.stage with wrapper injection")
else:
    print("Already patched or target not found")
