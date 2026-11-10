# BlogForge Deployment Guide (Vercel & Railway/Render)

This guide details how to deploy BlogForge for maximum speed, global edge CDN caching, and high availability.

---

## Architecture Options

### Option A: Serverless Deployment on Vercel (Recommended for Fast Load)
Vercel hosts the entire application including serverless PHP execution via `vercel.json` and serves all assets through Vercel's global Edge Network.

#### 1. Requirements:
- A free [MongoDB Atlas](https://www.mongodb.com/atlas) cluster (M0 free tier).
- A free [Cloudinary](https://cloudinary.com) account (for image hosting).
- A [Vercel](https://vercel.com) account.

#### 2. Vercel Configuration (`vercel.json`)
The included `vercel.json` automatically configures:
- `vercel-php@0.7.3` community serverless runtime.
- Long-term asset caching headers for `/assets/*` (`max-age=31536000, immutable`).
- Clean URL rewrites for all PHP endpoints.

#### 3. Environment Variables in Vercel Dashboard:
In your Vercel Project Settings → **Environment Variables**, add:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-project.vercel.app
APP_KEY=your_64_character_random_hex_key_here
MONGODB_URI=mongodb+srv://<username>:<password>@cluster0.xxxxx.mongodb.net/blogforge?retryWrites=true&w=majority
CLOUDINARY_URL=cloudinary://<api_key>:<api_secret>@<cloud_name>
```

#### 4. Deploying via Vercel CLI:
```bash
npm install -g vercel
vercel --prod
```

---

### Option B: Decoupled Architecture (Static Frontend on Vercel + Backend on Railway / Render)
If your PHP runtime has specialized native C-extensions (such as custom PECL drivers) or you prefer persistent processes without cold starts:

1. **Backend on Railway or Render:**
   - Deploy using the included `Dockerfile` to [Railway](https://railway.app) or [Render](https://render.com).
   - Set environment variables (`MONGODB_URI`, `APP_KEY`, `APP_URL`).
   - The backend serves JSON API endpoints (`/api/*`).

2. **Frontend on Vercel / Netlify:**
   - Static assets (`assets/css`, `assets/js`, `assets/img`) are served directly at the edge.
   - API calls in `assets/js/app.js` point to your Railway/Render backend URL.

---

## Client-Side Image Compression & Cloudinary Setup

BlogForge provides 3 optimized tiers for images:
1. **Cloudinary (Best):** Add `CLOUDINARY_URL` to `.env`. Images uploaded through the editor are automatically pushed to Cloudinary's global media CDN.
2. **In-Browser WebP Compression:** Images are compressed to ~120-140KB WebP using HTML5 Canvas before upload.
3. **Unsplash Integration:** Writers can paste any high-resolution Unsplash photo URL. BlogForge automatically injects responsive parameters (`auto=format&fit=crop&w=1200&q=80`).

---

## Health Check & Verification
Once deployed, verify:
- `https://your-domain/` — 6-section landing page with trending and community stories.
- `https://your-domain/explore.php` — Search and category filters.
- `https://your-domain/about.php` — Architecture and philosophy.
- `https://your-domain/faq.php` — Help center and interactive accordion.
- `https://your-domain/contact.php` — Contact form with rate-limiting.
