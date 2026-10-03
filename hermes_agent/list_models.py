import urllib.request
import json

api_key = "AIzaSyC1e_cMdjNglsfO-JYhzsR9-kWQRvyllWg"
url = f"https://generativelanguage.googleapis.com/v1beta/models?key={api_key}"

try:
    with urllib.request.urlopen(url) as resp:
        data = json.loads(resp.read().decode())
        for m in data.get('models', []):
            if 'generateContent' in m.get('supportedGenerationMethods', []):
                print(m['name'], "|", m.get('displayName', ''))
except Exception as e:
    print("Error:", e)
