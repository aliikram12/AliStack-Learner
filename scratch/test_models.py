import anthropic

client = anthropic.Anthropic(
    api_key='sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt',
    base_url='https://agentrouter.org'
)

test_models = [
    'claude-3-haiku-20240307',
    'claude-3-5-haiku-20241022',
    'claude-3-5-haiku',
    'claude-3-haiku',
    'claude-3-7-sonnet-20250219',
    'claude-3-sonnet-20240229',
    'claude-3-opus-20240229',
    'gpt-4o',
    'gpt-4o-mini',
    'deepseek-chat',
    'deepseek-v3',
    'glm-4'
]

for m in test_models:
    try:
        resp = client.messages.create(
            model=m,
            max_tokens=15,
            messages=[{'role': 'user', 'content': 'Hi'}]
        )
        print(f"MODEL {m}: SUCCESS! Output: {resp.content[0].text}")
        break
    except Exception as e:
        print(f"MODEL {m}: Failed -> {e}")
