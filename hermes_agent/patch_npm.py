with open('/home/wabotweb/.hermes/hermes-agent/pm/packages.py', 'r') as f:
    text = f.read()

target = """        node_bin = node.binary(store.entry(node_fact["entry"]), target)
        if node_bin is None or not node_bin.is_file():
            raise InstallError(self.name, "node's entry is missing its binary")
        win = target.startswith("win32")
        bundled_cli = (
            node_bin.parent / "node_modules" / "npm" / "bin" / "npm-cli.js"
            if win
            else node_bin.parent.parent / "lib" / "node_modules" / "npm" / "bin" / "npm-cli.js"
        )"""

replacement = """        # Use system nodejs 22 to avoid libatomic missing on RHEL 9
        if Path("/opt/cpanel/ea-nodejs22/bin/node").exists():
            node_bin = Path("/opt/cpanel/ea-nodejs22/bin/node")
            bundled_cli = Path("/opt/cpanel/ea-nodejs22/lib/node_modules/npm/bin/npm-cli.js")
        else:
            node_bin = node.binary(store.entry(node_fact["entry"]), target)
            if node_bin is None or not node_bin.is_file():
                raise InstallError(self.name, "node's entry is missing its binary")
            win = target.startswith("win32")
            bundled_cli = (
                node_bin.parent / "node_modules" / "npm" / "bin" / "npm-cli.js"
                if win
                else node_bin.parent.parent / "lib" / "node_modules" / "npm" / "bin" / "npm-cli.js"
            )"""

if target in text:
    text = text.replace(target, replacement, 1)
    with open('/home/wabotweb/.hermes/hermes-agent/pm/packages.py', 'w') as f:
        f.write(text)
    print("PATCHED npm runner with system ea-nodejs22")
else:
    print("TARGET NOT FOUND in packages.py")
