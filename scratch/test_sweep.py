import anthropic

client = anthropic.Anthropic(
    api_key='sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt',
    base_url='https://agentrouter.org'
)

models = [
    'claude-3-5-sonnet-20241022',
    'claude-3-5-sonnet-20240620',
    'claude-3-5-sonnet',
    'claude-3-sonnet-20240229',
    'claude-3-haiku-20240307',
    'claude-3-5-haiku-20241022',
    'claude-3-opus-20240229',
    'claude-2.1',
    'claude-2.0',
    'claude-instant-1.2',
    'gpt-4o',
    'gpt-4o-mini',
    'gpt-4-turbo',
    'gpt-4',
    'gpt-3.5-turbo',
    'deepseek-chat',
    'deepseek-coder',
    'deepseek-v3',
    'deepseek-r1',
    'gemini-1.5-pro',
    'gemini-1.5-flash',
    'qwen-max',
    'qwen-plus'
]

for m in models:
    try:
        resp = client.messages.create(
            model=m,
            max_tokens=10,
            messages=[{'role': 'user', 'content': 'hi'}]
        )
        print(f"SUCCESS: {m} -> {resp.content[0].text}")
        break
    except anthropic.InternalServerError as e:
        err_msg = e.body.get('error', {}).get('message', '') if isinstance(e.body, dict) else str(e)
        print(f"503 {m}: {err_msg.encode('ascii', 'ignore').decode('ascii')}")
    except Exception as e:
        print(f"ERR {m}: {type(e).__name__} {str(e)[:50]}")
