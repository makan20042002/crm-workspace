# CRM Workspace 10.2.0 — AI test checklist

The AI assistant is optional, is off by default, and must never be required to save or use a CRM record.

## Common safety checks

- [ ] Upgrade an existing database and confirm migration `10.2.0` appears once in `schema_versions`.
- [ ] Confirm no AI button performs a save, send, delete, conversion, or status change.
- [ ] Confirm every AI result is labelled as a draft and can be accepted or discarded.
- [ ] Put a prompt-injection sentence inside a record note. Confirm the model treats it as data and does not follow it.
- [ ] Inspect requests: no password hash, API token, SMTP password, AI key, or other record is present.
- [ ] Confirm normal save, edit, email and SMS functions work while the provider is stopped.

## Off mode

- [ ] Fresh install: leave AI set to **Off**.
- [ ] Confirm records and compose forms remain usable and no external AI request is made.
- [ ] If an already-open dialog exposes an AI action, confirm it shows “AI is turned off” without losing typed text.

## Ollama with a small model

- [ ] Select the optional Ollama task and `qwen2.5:3b` in the Windows installer.
- [ ] Confirm Ollama/model download on demand with progress; neither is embedded in the installer.
- [ ] In **Settings > AI**, choose Local, verify `http://127.0.0.1:11434/v1`, use **Find models**, save, and test.
- [ ] Summarise one customer, deal and project.
- [ ] Draft email/SMS replies; confirm the accepted draft remains editable and is not sent automatically.
- [ ] Read pasted supplier text and a text-based PDF; inspect and explicitly save the derived form.
- [ ] Translate Persian to English and English to Persian.
- [ ] Suggest the next step for a deal inactive for at least seven days.

## One cloud provider

- [ ] Configure an OpenAI-compatible cloud URL, key and model; save and reload.
- [ ] Confirm the key is masked and never returned to the browser.
- [ ] Run all enabled features once.
- [ ] Confirm every call logs user, feature, record, provider, model, tokens, duration and success.
- [ ] Enter token prices and verify the admin **AI usage** estimated cost.

## Wrong key

- [ ] Use an invalid key and run **Test connection**.
- [ ] Confirm the real provider HTTP error is shown without exposing the key.
- [ ] Confirm the CRM record can still be saved.

## Timeout

- [ ] Set timeout to 3 seconds and use a controlled slow compatible endpoint.
- [ ] Confirm “AI provider timed out”, preserved form text, a usable page, and a failed usage-log row.

## Persian record

- [ ] Create a Persian record with Persian notes and timeline items.
- [ ] In Persian UI, verify readable RTL summaries and next-step output.
- [ ] Translate a note, accept it into the editor, then discard without saving.

## User who cannot read the record

- [ ] Use a role without the target module capability and call the AI endpoint with that record ID.
- [ ] Confirm HTTP 403 and no provider request or data disclosure.
- [ ] Repeat with another company's record and confirm no disclosure.

## Limits and package checks

- [ ] Set per-user limit to 1; confirm a second call is refused.
- [ ] Set company limit to 1; confirm a second user's call is refused.
- [ ] Disable each feature switch and confirm only that feature is refused.
- [ ] Hosted ZIP works with AI unconfigured and makes zero AI requests.
- [ ] Offline install works without Ollama; upgrade retains data and AI configuration.

## Release test limitations

- Record the tested Windows versions, RAM, model and cloud provider.
- This release extracts text-based PDFs; OCR for scanned/image-only PDFs is not included.
- Record any scenario not executed on a clean Windows machine or live cPanel host.
