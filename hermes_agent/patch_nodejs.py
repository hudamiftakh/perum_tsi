with open('/home/wabotweb/.hermes/hermes-agent/pm/packages.py', 'r') as f:
    text = f.read()

target = """class Nodejs(_BionicDebArm, BinaryPackage, DebPackage):
    \"\"\"nodejs.org tarballs for glibc/mac/win, unofficial-builds for musl;
    the Termux main-repo nodejs .deb for bionic (same major line, termux-built).\"\"\"

    name = "node" """

# Let's search by class Nodejs
idx = text.find('class Nodejs(')
if idx != -1:
    insert_point = text.find('name = "node"', idx)
    if insert_point != -1:
        text = text[:insert_point] + 'probe_version = False\n    ' + text[insert_point:]
        with open('/home/wabotweb/.hermes/hermes-agent/pm/packages.py', 'w') as f:
            f.write(text)
        print("PATCHED Nodejs with probe_version = False")
    else:
        print("name = 'node' not found")
else:
    print("class Nodejs not found")
