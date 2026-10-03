with open('/home/wabotweb/install_hermes.sh', 'r') as f:
    text = f.read()

target = 'pm_args+=(--without node --without npm)'
if target in text:
    text = text.replace(target, '', 1)
    with open('/home/wabotweb/install_hermes.sh', 'w') as f:
        f.write(text)
    print("REVERTED --without node --without npm from install_hermes.sh")
else:
    print("NOT FOUND")
