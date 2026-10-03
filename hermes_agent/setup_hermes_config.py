import re

# 1. Update .env
env_path = '/home/wabotweb/.hermes/.env'
with open(env_path, 'r') as f:
    env_content = f.read()

# Set GEMINI_API_KEY
if 'GEMINI_API_KEY=' in env_content:
    env_content = re.sub(r'#?\s*GEMINI_API_KEY=.*', 'GEMINI_API_KEY=AIzaSyC1e_cMdjNglsfO-JYhzsR9-kWQRvyllWg', env_content)
else:
    env_content += '\nGEMINI_API_KEY=AIzaSyC1e_cMdjNglsfO-JYhzsR9-kWQRvyllWg\n'

# Set TELEGRAM_BOT_TOKEN
if 'TELEGRAM_BOT_TOKEN=' in env_content:
    env_content = re.sub(r'#?\s*TELEGRAM_BOT_TOKEN=.*', 'TELEGRAM_BOT_TOKEN=8918199149:AAFQOhXSdctb2G6yTmN9NRCkTIa-al4CQH8', env_content)
else:
    env_content += '\nTELEGRAM_BOT_TOKEN=8918199149:AAFQOhXSdctb2G6yTmN9NRCkTIa-al4CQH8\n'

# Set TELEGRAM_ALLOWED_USERS
if 'TELEGRAM_ALLOWED_USERS=' in env_content:
    env_content = re.sub(r'#?\s*TELEGRAM_ALLOWED_USERS=.*', 'TELEGRAM_ALLOWED_USERS=*', env_content)
else:
    env_content += '\nTELEGRAM_ALLOWED_USERS=*\n'

with open(env_path, 'w') as f:
    f.write(env_content)
print("Updated .env successfully!")

# 2. Update config.yaml
cfg_path = '/home/wabotweb/.hermes/config.yaml'
with open(cfg_path, 'r') as f:
    cfg = f.read()

# Replace model.default and model.provider
cfg = re.sub(r'default:\s*"anthropic/claude-opus-4.6"', 'default: "gemini-2.5-flash"', cfg)
cfg = re.sub(r'provider:\s*"auto"', 'provider: "gemini"', cfg)

with open(cfg_path, 'w') as f:
    f.write(cfg)
print("Updated config.yaml successfully!")
