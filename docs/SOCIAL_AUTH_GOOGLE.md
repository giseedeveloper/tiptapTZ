# Google OAuth setup (TIPTAP TZ & SA)

Use this guide to enable **Google sign-in / register** on both Laravel apps. Facebook and Apple buttons are already visible in the UI and marked **Soon** until credentials are added.

## 1. Google Cloud Console

1. Open [Google Cloud Console](https://console.cloud.google.com/)
2. Create or select a project (e.g. `TIPTAP Tanzania`, `TIPTAP South Africa`)
3. **APIs & Services → OAuth consent screen**
   - User type: **External** (or Internal if Workspace)
   - App name: `TIPTAP`
   - Support email: your team email
   - Scopes: `email`, `profile`, `openid`
4. **APIs & Services → Credentials → Create credentials → OAuth client ID**
   - Application type: **Web application**

## 2. Redirect URIs (add all environments)

| Environment | Redirect URI |
|-------------|--------------|
| TZ production | `https://tiptapafrica.co.tz/auth/google/callback` |
| SA production | `https://tiptapafrica.co.za/auth/google/callback` |
| Local TZ | `http://127.0.0.1:8000/auth/google/callback` |
| Local SA | `http://127.0.0.1:8001/auth/google/callback` |

**Authorized JavaScript origins** (optional for web):

- `https://tiptapafrica.co.tz`
- `https://tiptapafrica.co.za`
- `http://127.0.0.1:8000`
- `http://localhost:8000`

## 3. `.env` variables

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Run after updating env on server:

```bash
php artisan config:cache
```

## 4. User flows

### Register (restaurant / waiter)

1. User opens `/register-restaurant` or `/register-waiter`
2. Clicks **Register with Google** (top section)
3. Google OAuth → account created without password
4. **Restaurant:** `/register-restaurant/complete` — restaurant name, location, phone
5. **Waiter:** `/register-waiter/complete` — phone, optional location
6. Existing email/password accounts are **not affected**

### Login

1. User opens `/login`
2. **Google:** one-click login (no password)
3. **Email:** password required (legacy + manual registrations)

### Rules

| Account type | Login method |
|--------------|--------------|
| Registered with email + password | Email + password only |
| Registered with Google | Google button only (no password) |
| Existing restaurants before this change | Unchanged (`auth_provider=email`) |

## 5. Database migration

```bash
php artisan migrate
```

Adds `auth_provider`, `auth_provider_id` to `users` and allows `password` to be null for OAuth users.

## 6. Routes

| Route | Purpose |
|-------|---------|
| `GET /auth/google/redirect?role=manager&intent=register` | Start Google OAuth |
| `GET /auth/google/callback` | Google callback |
| `GET /register-restaurant/complete` | Finish restaurant after Google |
| `GET /register-waiter/complete` | Finish waiter after Google |

## 7. Facebook & Apple (later)

When ready, add credentials to `.env` and the same redirect pattern:

- `https://your-domain/auth/facebook/callback`
- `https://your-domain/auth/apple/callback`

Buttons will automatically become active when `FACEBOOK_CLIENT_*` or `APPLE_CLIENT_*` are set.
