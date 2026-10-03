import json

with open('/home/wabotweb/.hermes/hermes-agent/pm/lock.json') as f:
    d = json.load(f)

print(json.dumps(d['packages']['node'], indent=2))
