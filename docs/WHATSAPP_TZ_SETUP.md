# WhatsApp setup — TAPTAP TZ

Laravel: `https://tiptapafrica.co.tz`  
Bot notify: `https://wa-notify.tiptapafrica.co.tz/notify`  
Meta webhook: `https://tiptapafrica.co.tz/api/whatsapp/webhook`

## 1. Laravel `.env` / `.env.docker`

```env
TIPTAP_MARKET=tz
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_VERIFY_TOKEN=
WHATSAPP_APP_SECRET=
WHATSAPP_BOT_NOTIFY_URL=https://wa-notify.tiptapafrica.co.tz/notify
WHATSAPP_BOT_NOTIFY_SECRET=
BOT_TOKEN=
```

Generate `BOT_TOKEN` at `/admin/bots` (bot_service user).

## 2. Bot VPS `.env`

```env
TIPTAP_MARKET=tz
API_BASE_URL=https://tiptapafrica.co.tz/api/bot
BOT_TOKEN=<same as Laravel>
NOTIFY_SECRET=<same as WHATSAPP_BOT_NOTIFY_SECRET>
USE_INTERACTIVE_MENU=true
```

Copy Meta vars from Laravel into bot `.env`.

## 3. Deploy

```bash
# Laravel (from TAPTAP tz repo)
./deploy-tz-vps.sh

# Bot (from TipTap_tz_bot repo)
./scripts/deploy-bot-all.sh
```

## 4. Verify

```bash
php artisan whatsapp:doctor --probe
php artisan test --filter=WhatsApp
```

Send `hi` on WhatsApp — expect home buttons (Menu, Pay Bill, More).
