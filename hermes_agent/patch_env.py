with open('/home/wabotweb/.hermes/hermes-agent/pm/environment.py', 'r') as f:
    content = f.read()

target = 'base.update(bridged_index_settings(os.environ))'
replacement = """base.update(bridged_index_settings(os.environ))
    base['RAYON_NUM_THREADS'] = '1'
    base['UV_CONCURRENT_DOWNLOADS'] = '1'
    base['UV_CONCURRENT_INSTALLS'] = '1'
    base['UV_CONCURRENT_BUILDS'] = '1'"""

if target in content:
    content = content.replace(target, replacement, 1)
    with open('/home/wabotweb/.hermes/hermes-agent/pm/environment.py', 'w') as f:
        f.write(content)
    print("PATCHED SUCCESS")
else:
    print("TARGET NOT FOUND")
