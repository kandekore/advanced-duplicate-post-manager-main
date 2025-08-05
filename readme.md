# Advanced Duplicate Post Manager

**Contributors:** Darren Kandekore  
**Tags:** duplicates, redirect, media, htaccess, slug, CSV, SEO, admin  
**Requires at least:** 5.0  
**Tested up to:** 6.5  
**Requires PHP:** 7.4  
**Stable tag:** 3.2  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

A powerful tool for WordPress administrators to detect and manage duplicate content across posts, pages, categories, media, and custom post types. Assign 301 redirects, clean up media, and manage `.htaccess` rules from a user-friendly interface.

---

## 🔧 Features

- ✅ Detect duplicates by **title** and **slug**
- ✅ Works with:
  - Posts
  - Pages
  - Custom Post Types
  - Categories
  - Media (by filename and file size)
- ✅ Bulk delete duplicate entries
- ✅ Assign per-item **301 redirects**
- ✅ Generate and manage `.htaccess` redirect rules
- ✅ View and download `.htaccess` from the admin panel
- ✅ One-click **backup** and **restore** for `.htaccess`
- ✅ Automatically generate **CSV export** of detected duplicates
- ✅ Compatible with PHP 8.1+

---

## 📥 Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin via **Plugins > Installed Plugins**
3. Go to **Tools > Duplicate Post Manager**

---

## 🚀 How to Use

### 📍 Step 1: Scan for Duplicates

- Navigate to **Tools > Duplicate Post Manager**
- Select a content type:
  - Posts, Pages, Custom Post Types
  - Media Library
  - Categories
- Click **Scan for Duplicates**

### ✅ Step 2: Review and Select Duplicates

- Duplicates will appear in a table
- For each:
  - Check the box to delete
  - (Posts/Pages only) Choose a redirect target:
    - Select from other duplicates
    - Or enter a manual URL

> ⚠️ Media and categories are delete-only — no redirects.

### 🗑️ Step 3: Process

- Click **Delete Selected & Redirect**
- Posts will be sent to trash
- Redirects will be saved and added to `.htaccess` output

---

## 📜 .htaccess Management

Go to **Tools > .htaccess Manager** to:

- View generated redirect rules
- Write them directly into your real `.htaccess` file
- Clear all stored redirects
- Download `.htaccess` backup

---

## 💾 .htaccess Backup/Restore

Go to **Tools > .htaccess Backups** to:

- One-click backup your current `.htaccess` to `/uploads/htaccess-backups/`
- Restore the last backup anytime

---

## 📥 CSV Export

- Each time you scan, a CSV file is saved to `/wp-content/uploads/advanced-duplicate-posts.csv`
- After scanning, a **Download Duplicates CSV** button will appear
- Useful for keeping audit logs or pre-approval before deletion

---

## 🔒 Best Practices

- 🔄 Always **back up `.htaccess`** before writing changes
- 📁 Only assign redirects to valid, working URLs
- ✅ Let the plugin check for 404s — it won’t save invalid redirects
- 🧹 Delete media duplicates with caution — this cannot be undone
- 🧪 Test in staging before cleaning large content batches

---

## 🧠 Why Use This Plugin?

- Prevent SEO penalties from duplicate content
- Safely redirect deleted pages instead of serving 404s
- Keep your database and media library lean and organized
- Avoid slug collisions and orphaned content
- Maintain editorial and audit control with CSV logs

---

## 💼 Use Cases

- Bulk-imported WordPress sites with many auto-generated posts
- Migrated content from older CMSs
- eCommerce or directory sites with repeated titles or categories
- Media-heavy sites needing cleanup of duplicate images

---

## ❓ FAQ

**Q: Can I use this for WooCommerce products?**  
A: Yes! Products are a custom post type. Just select them from the dropdown.

**Q: Are deleted posts permanently removed?**  
A: No — posts/pages are moved to trash. Media is permanently deleted.

**Q: Does it affect SEO?**  
A: In a good way! 301 redirects preserve SEO value by avoiding 404s.

---

## 🧩 Roadmap / Ideas

- [ ] Automatic scheduled duplicate scans
- [ ] Email CSV audit reports to admins
- [ ] WooCommerce product image deduplication
- [ ] Integration with Redirection plugin and Rank Math

---

## 📜 License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

## 👤 Author

[Darren Kandekore](https://github.com/dkandekore)  
Built with care to keep your WordPress content clean, SEO-friendly, and efficient.
