# UltraDev Search

Fast autocomplete search module with **voice search** and **image search** for OpenMage / Magento 1.x — based on [Bubble Search](https://github.com/degdigital/magento-custom-search).

## Features

- **Autocomplete** — real-time product suggestions as the user types
- **Fuzzy matching** — finds products even with typos or accents
- **SKU search** — matches by SKU in addition to product name
- **Voice search** — microphone button using the Web Speech API (HTTPS required)
- **Image search** — upload a photo and let AI identify the product; supports Google Vision and Gemini Flash
- **Local storage cache** — reduces server requests by caching product data in the browser
- **Mobile responsive** — simplified card layout on small screens
- **Dual API key** — automatic fallback to a secondary key when the monthly limit is reached

## Requirements

- OpenMage / Magento 1.x
- PHP >= 7.4

## Installation

### Via Composer (recommended)

```bash
composer require ultradev/magento-search
```

### Via modman

```bash
modman clone https://github.com/LuizSantos22/ultradev-search
```

### Manual

Download the zip and copy the files following the structure in `modman`.

## Configuration

Go to **System → Configuration → UltraDev → UltraDev Search**.

### General Settings

| Field | Description | Default |
|-------|-------------|---------|
| Enable Autocomplete | Turns the module on/off | Yes |
| Suggestions Limit | Max products shown | 5 |
| Minimum Length | Characters before suggestions appear | 1 |
| Cache Lifetime | Seconds to cache in local storage | 86400 |
| Use Local Storage | Cache product data in the browser | Yes |
| Enable jQuery | Disable if your theme already loads jQuery | Yes |

### Image Search

| Field | Description |
|-------|-------------|
| Enable Image Search | Turns image search on/off |
| AI Provider | Google Vision (1,000 req/month free) or Gemini Flash (1,500 req/day free) |
| API Key (Primary) | Primary key for the selected provider |
| API Key (Fallback) | Used automatically when the primary key approaches the limit |
| Monthly Request Limit | Switch to fallback key at this count (default: 950) |

## Providers

| Provider | Free tier | Best for |
|----------|-----------|----------|
| **Google Vision** | 1,000 req/month | Brand and logo recognition |
| **Gemini Flash** | 1,500 req/day | Generative product identification |

## License

MIT — [UltraDev](https://ultradev.com.br)
