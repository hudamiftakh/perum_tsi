with open('/home/wabotweb/install_hermes.sh', 'r') as f:
    text = f.read()

target = '[ "$SKIP_COMPUTER_USE" = true ] && pm_args+=(--without cua-driver)'
replacement = """[ "$SKIP_COMPUTER_USE" = true ] && pm_args+=(--without cua-driver)
    pm_args+=(--without node --without npm)"""

if target in text:
    text = text.replace(target, replacement, 1)
    with open('/home/wabotweb/install_hermes.sh', 'w') as f:
        f.write(text)
    print("PATCHED install_hermes.sh with --without node --without npm")
else:
    print("TARGET NOT FOUND in install_hermes.sh")
