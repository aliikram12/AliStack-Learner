import anthropic
import json

client = anthropic.Anthropic(
    api_key='sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt',
    base_url='https://agentrouter.org'
)

# Use raw httpx client inside anthropic client to query /v1/models
resp = client._client.get("https://agentrouter.org/v1/models", headers={"Authorization": f"Bearer {client.api_key}"})
print("STATUS:", resp.status_code)
try:
    data = resp.json()
    print("MODELS:")
    for item in data.get('data', []):
        print(item.get('id'))
except Exception as e:
    print(resp.text[:500])
