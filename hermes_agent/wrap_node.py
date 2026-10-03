import os
import shutil

node_path = '/home/wabotweb/.hermes/tools/node-26.7.0-linux-x64/bin/node'
orig_path = node_path + '.orig'

if os.path.exists(node_path) and not os.path.exists(orig_path):
    shutil.move(node_path, orig_path)

wrapper_content = """#!/bin/bash
exec /opt/cpanel/ea-nodejs22/bin/node "$@"
"""

with open(node_path, 'w') as f:
    f.write(wrapper_content)

os.chmod(node_path, 0o755)
print("Node wrapped successfully!")
