with open('/home/wabotweb/.hermes/hermes-agent/hermes_cli/source_build.py', 'r') as f:
    text = f.read()

target1 = 'return ensure("npm", base_env=env, explicit=explicit).env'
replacement1 = """res_env = ensure("npm", base_env=env, explicit=explicit).env
    if os.path.exists("/opt/cpanel/ea-nodejs22/bin"):
        res_env["PATH"] = "/opt/cpanel/ea-nodejs22/bin:" + res_env.get("PATH", "")
    return res_env"""

target2 = """    run_contained(
        [shutil.which("node", path=env["PATH"]), str(project_root / script), *args],
        label, hide=lambda line: line.lower().startswith("npm warn"), indent="  ",
        cwd=project_root, env=env,
    )"""

replacement2 = """    node_bin = "/opt/cpanel/ea-nodejs22/bin/node" if os.path.exists("/opt/cpanel/ea-nodejs22/bin/node") else shutil.which("node", path=env["PATH"])
    if os.path.exists("/opt/cpanel/ea-nodejs22/bin"):
        env["PATH"] = "/opt/cpanel/ea-nodejs22/bin:" + env.get("PATH", "")
    run_contained(
        [node_bin, str(project_root / script), *args],
        label, hide=lambda line: line.lower().startswith("npm warn"), indent="  ",
        cwd=project_root, env=env,
    )"""

if target1 in text and target2 in text:
    text = text.replace(target1, replacement1, 1)
    text = text.replace(target2, replacement2, 1)
    with open('/home/wabotweb/.hermes/hermes-agent/hermes_cli/source_build.py', 'w') as f:
        f.write(text)
    print("PATCHED source_build.py successfully!")
else:
    print("TARGET NOT FOUND in source_build.py")
