#!/usr/bin/env python3
"""
AliStack Learner - AI Bridge Runner
Runs requests via Anthropic / AgentRouter Python SDK with TLS compliance.
"""
import sys
import json

def main():
    try:
        raw_input = sys.stdin.read()
        if not raw_input:
            print(json.dumps({"success": False, "error": "Empty input payload"}))
            return

        data = json.loads(raw_input)
        api_key = data.get("api_key", "").strip()
        base_url = data.get("base_url", "https://agentrouter.org").strip()
        model = data.get("model", "claude-3-5-sonnet-20241022").strip()
        messages = data.get("messages", [])
        system = data.get("system", "")
        max_tokens = int(data.get("max_tokens", 1200))

        if not api_key:
            print(json.dumps({"success": False, "error": "API Key is required"}))
            return

        import anthropic
        client = anthropic.Anthropic(api_key=api_key, base_url=base_url)

        # Build kwargs
        kwargs = {
            "model": model,
            "max_tokens": max_tokens,
            "messages": messages
        }
        if system:
            kwargs["system"] = system

        resp = client.messages.create(**kwargs)
        reply = resp.content[0].text if resp.content else ""
        
        print(json.dumps({
            "success": True,
            "reply": reply,
            "prompt_tokens": getattr(resp.usage, "input_tokens", 0),
            "completion_tokens": getattr(resp.usage, "output_tokens", 0)
        }))
    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e)
        }))

if __name__ == "__main__":
    main()
