import json

path = '/home/wabotweb/.hermes/installs/51a6ed55f1096352/declined-packages.json'
with open(path) as f:
    d = json.load(f)

for pkg in ["node", "npm", "bws", "chromium", "dmgbuild", "iron-proxy", "llamacpp-cpu", "llamacpp-cuda", "llamacpp-hip", "llamacpp-metal", "llamacpp-vulkan", "termux-docker", "tirith"]:
    if pkg not in d["declined"]:
        d["declined"].append(pkg)

with open(path, 'w') as f:
    json.dump(d, f, indent=2)

print("Updated declined packages:", d["declined"])
