# Zoro – PHP Anime Streaming Site

A self-hosted, SEO-friendly anime streaming platform built with PHP, MySQL and the [AniList GraphQL API](https://anilist.co).  
Forked and extended from the original work by **[KiriXen](https://github.com/KiriXen)**, this version modernises URLs, removes server-side caching, improves user-history tracking and adds fresh endpoints such as Upcoming, Ongoing & Trending lists.

---

## ✨  Features

* SEO-friendly routes:
  * Anime details – `/anime/{id}/{slug}`
  * Episode watch  – `/watch/{id}/{slug}/episode-{number}`
* Clean, responsive UI (Bootstrap 4 + FontAwesome 6).
* AniList GraphQL integration for metadata, recommendations & upcoming titles.
* Continue-Watching list stored in MySQL per user.
* Dynamic sitemaps (SUB, DUB, Chinese, Ongoing & All Anime).
* View-, like- & dislike-counters with prepared-statement SQL.
* Zero file-system caching – every request is live from AniList.
* Discord community support.

---

## 🗂  Project Structure (excerpt)

```
htdocs/
├─ _config.php           # Site-wide configuration
├─ _php/                 # Helper libraries & API wrappers
│  └─ anilist_api.php    # AniList GraphQL helper (no cache)
├─ home.php              # Landing page (Trending, Ongoing, Latest, Upcoming)
├─ streaming.php         # Episode watch page
├─ latest/               # Latest release category pages
├─ sitemaps/             # Dynamic XML/HTML sitemaps
└─ files/                # Static assets (CSS, JS, images)
```

---

## 🚀  Quick Start

1. **Clone & install dependencies**
   ```bash
   git clone https://github.com/yourname/zoro-php.git
   cd zoro-php/htdocs
   composer install   # only if you add composer packages
   ```

2. **Create the database** (MySQL 5.7+ / MariaDB 10.4+ recommended):
   ```sql
   CREATE DATABASE anime DEFAULT CHARACTER SET utf8mb4;

   -- table for user history
   CREATE TABLE user_history (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id VARCHAR(64),
     anime_id VARCHAR(32),
     anime_title VARCHAR(255),
     anime_ep INT,
     anime_image VARCHAR(255),
     anime_release VARCHAR(10),
     dubOrSub VARCHAR(8),
     anime_type VARCHAR(20),
     watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   -- table for page views / likes
   CREATE TABLE pageview (
     id VARCHAR(64) PRIMARY KEY,
     like_count INT DEFAULT 0,
     dislike_count INT DEFAULT 0
   );
   ```

3. **Configure your environment** – copy the example below into `_config.php` or `.env`.

4. **Serve locally** (XAMPP / Apache / nginx + PHP-FPM) and visit `http://localhost:3000/home`.

---

## 🔧  Configuration Example (`_config.php`)

```php
<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'anime') or die('Connection failed');

// Site basics
$websiteTitle = 'Zoro';
$websiteUrl   = 'http://localhost:3000';
$version      = '0.1';

// Social / external links
$discord = 'https://discord.gg/7JKJSbnHqf';  // Community support
$github  = 'https://github.com/KiriXen';      // Original author credit
$twitter = 'https://x.com/KiriX3n';
$contactEmail = 'example@gmail.com';

// Disqus embed (leave blank to disable)
$disqus = 'https://YOURSHORTNAME.disqus.com/embed.js';

// Optional self-hosted mapper API (leave empty if unused)
$api = 'https://your-api.tld/api';

// Assets
$websiteLogo = $websiteUrl . '/files/images/logo_zoro.png';
$banner      = $websiteUrl . '/files/images/banner.png';
```

---

## 🤝  Contributing

Pull requests are welcome! Please open an issue first to discuss what you would like to change.

1. Fork the repo & create your branch: `git checkout -b feature/fooBar`  
2. Commit your changes with clear messages.  
3. Push to the branch: `git push origin feature/fooBar`  
4. Open a pull request.

---

## 📃  License

This project retains the original **MIT License** from [KiriXen](https://github.com/KiriXen).  
Please see `LICENSE` for details.

---

## 🙏  Credits

* **[KiriXen](https://github.com/KiriXen)** – Original creator & inspiration.  
* AniList – GraphQL anime database API.
* Bootstrap & FontAwesome – UI framework & icons.

Join our Discord for help or discussion: **https://discord.gg/7JKJSbnHqf**
