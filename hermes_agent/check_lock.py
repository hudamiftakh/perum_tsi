import json

with open('/home/wabotweb/.hermes/hermes-agent/pm/lock.json') as f:
    d = json.load(f)

print("Lock packages:", list(d.get('packages', {}).keys()))
