# CRM Workspace: AI guide

Applies to version 10.2.0.

The AI assistant is optional. It is switched off after installation, and every part of the CRM works without it. When it is on, it only produces drafts: it never saves, sends, deletes or changes a record. A person always reviews the result and decides what to do with it.

## 1. What the AI can do

There is no chat box. The AI is reached through five buttons, each doing one job.

| Button | Where it appears | What it does |
|---|---|---|
| Summarise | Customer, opportunity and project pages | Writes one short paragraph from the record and its recent timeline |
| Suggest next step | Opportunity page | Proposes one next action. Works only when the deal has had no activity for 7 days |
| Read supplier quote | RFQ page | Reads pasted text or a PDF and fills a supplier-quote form for review |
| Draft reply | Email and SMS compose window | Turns a one-line instruction into an editable draft |
| Translate | Compose and add-note windows | Translates between Persian and English |

The buttons are marked with ✦ and appear only when AI is on and that feature is enabled.

## 2. Choose how to run it

| | Off | Local model (Ollama) | Cloud API |
|---|---|---|---|
| Cost | None | None after you have the PC | Paid per use |
| Internet needed | No | Only to download the model once | Yes, for every request |
| Where your data goes | Nowhere | Stays on your own PC | Sent to the provider you choose |
| Quality | n/a | Depends on the model and PC | Usually the best |
| Suits | Everyone at first | Offline Windows package | Hosting package |

- **Hosting package on shared hosting:** a local model cannot run there, so choose Off or Cloud API.
- **Offline package:** a local model keeps data inside the office. Cloud API also works if the PC has internet.

## 3. Open the AI settings

1. Sign in as an administrator.
2. Open **Settings**.
3. Press **✦ AI settings** at the top.

The installer also has an AI section, but leaving it Off and configuring it later is fine.

## 4. Set up a local model

### With the Windows offline installer

During installation, select the optional AI task and a model size. The installer downloads Ollama and the model; neither is bundled, so this step needs internet once.

| Size | Model offered |
|---|---|
| Small | `qwen2.5:3b` |
| Medium | `qwen2.5:7b` |
| Large | `qwen2.5:14b` |

Choose according to PC memory. Larger models usually write better but answer more slowly.

### By hand

1. Install Ollama from ollama.com.
2. Download a model in a terminal, for example:

```
ollama pull qwen2.5:3b
```

3. Leave Ollama running.

### Then, in AI settings

1. Set **Provider** to **Local model (Ollama)**.
2. Keep **Base URL** as `http://127.0.0.1:11434/v1`.
3. Press **Save**.
4. Press **Find models** and select a model.
5. Keep **Timeout** at 120 seconds or increase it for a slow PC.
6. Save, then press **Test connection**.

Save before Find models or Test connection; both use saved settings.

## 5. Set up a cloud API

A cloud API is a paid service separate from chat subscriptions.

1. Create an API account and payment method with a compatible provider.
2. Create an API key and treat it like a password.
3. Select **Cloud API** and enter the provider's OpenAI-compatible base URL ending in `/v1`, the API key, and exact model name.
4. Save, then test the connection.

The key is stored encrypted and is never shown again. Leave the key field empty to keep it when changing other settings. Check the provider's country availability and data-use terms before sending customer data.

## 6. Cost and usage

Providers charge per token. A page of text is roughly 500 to 700 tokens.

```
(input tokens × input price + output tokens × output price) ÷ 1,000,000
```

| Example | Tokens | Cost at $1 input / $5 output per million |
|---|---|---|
| Record and timeline | 4,000 input | $0.0040 |
| Returned summary | 500 output | $0.0025 |
| One request | | about $0.0065 |

| Team use | Requests per month | Estimated monthly cost |
|---|---|---|
| Light: 10 each working day | about 220 | about $1.50 |
| Normal: 50 each working day | about 1,100 | about $7 |
| Heavy: 200 each working day | about 4,400 | about $29 |

These estimates use the example prices above. Prices vary by provider and model and change over time. A long supplier quotation also uses more input tokens than a summary. There is no monthly fee in this example: with no requests, cost is zero.

### Keep cost under control

- Set company and per-user daily limits.
- Set a spending cap in the provider account.
- Disable features you do not need.
- Use a smaller model for summaries, drafts and translation when it is good enough.

### See what has been used

1. Enter the provider's current prices in **Input cost / million tokens** and **Output cost / million tokens**.
2. Press **AI usage** at the top of AI settings.

The usage page shows requests, successful calls, input and output tokens and estimated cost per day and user. The provider invoice remains the authoritative cost.

## 7. Using each feature

### Summarise

Open a customer, opportunity or project and press **✦ Summarise**. Accept copies the draft; Discard closes it. Nothing is saved to the record.

### Suggest next step

Open an opportunity and press **✦ Suggest next step**. It runs only when the deal has had no activity for seven days.

### Read supplier quote

1. Open the RFQ and press **✦ Read supplier quote**.
2. Paste text or select a text-based PDF up to 10 MB.
3. Review supplier, reference, currency, items, prices, freight and lead time.
4. Choose the supplier and press **Save** only after checking the original.

Scanned image-only PDFs contain no readable text; paste their text manually.

### Draft reply

In an email or SMS compose window, enter a one-line instruction. Accepting puts an editable draft in the message body; it is not sent automatically.

### Translate

Type or paste a message or note and press Translate. Accepting replaces the field with the translation.

## 8. Privacy and limits

- Only the open record, its permitted fields, up to 30 timeline entries and text entered for the request are sent.
- Passwords, API keys, other records and other users' data are never sent.
- Users can only use AI on records their role may read.
- Local-model data stays on the Ollama PC; cloud requests go to the chosen provider.
- Each call logs user, feature, record, provider, model, token counts, duration and success, but not the request text.
- AI can be wrong, especially with quotation numbers. Always review before saving or sending.

## 9. Troubleshooting

A failed AI request leaves the CRM page fully usable.

| Message | Cause | What to do |
|---|---|---|
| AI is turned off | Provider is Off | Choose Local or Cloud in AI settings |
| ai_not_configured | Base URL or model is empty | Fill both and save |
| AI connection failed | Wrong address or Ollama stopped | Check the URL and start Ollama |
| AI provider HTTP 401 | API key is invalid | Enter a valid key and save |
| AI provider HTTP 404 | Model or base URL is wrong | Copy both from provider documentation |
| AI provider HTTP 429 | Provider rate or spending limit reached | Wait or change the provider limit |
| AI provider timed out | The response exceeded timeout | Increase timeout or use a smaller local model |
| Your daily AI limit has been reached | User limit reached | Raise it or wait until tomorrow |
| Company daily AI limit reached | Company limit reached | Raise it or wait until tomorrow |
| This AI feature is disabled | Feature switch is off | Enable it in AI settings |
| The AI result was not valid quotation JSON | Model returned the wrong format | Retry, use a stronger model, or enter it manually |
| This deal has activity within the last 7 days | Deal is not inactive | No action is needed |

If a local model is slow or its Persian is poor, try the next model size or use a cloud API.

## 10. Quick checklist

- [ ] Decide: Off, Local or Cloud.
- [ ] Local: Ollama installed, model downloaded, timeout at least 120 seconds.
- [ ] Cloud: account, key, base URL, model and provider spending cap ready.
- [ ] Test connection succeeds.
- [ ] Daily limits are set.
- [ ] Token prices are entered for usage estimates.
- [ ] The team knows every AI output is a draft that must be checked.
