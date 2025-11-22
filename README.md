# 🌲 CMS Pohoda Antošovice

**Moderní content management systém pro naturistické kempy a podobná zařízení.**

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300f.svg?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/bootstrap-%23563D7C.svg?style=for-the-badge&logo=bootstrap&logoColor=white)

## ✨ Vlastnosti

### 🎨 **Frontend**
- **Moderní responzivní design** s Bootstrap 5
- **Glassmorphism efekty** a animace
- **SEO optimalizované** URL struktura
- **Mobilní-first** přístup

### ⚙️ **Admin rozhraní**
- **Správa stránek a článků** s WYSIWYG editorem
- **Upload obrázků** z disku nebo URL
- **Quick Links systém** - správa rychlých odkazů
- **Nastavení webu** - název, popis, kontakty
- **Bezpečné přihlášení** s reset hesla

### 🔐 **Bezpečnost**
- **Password hashing** s BCrypt
- **CSRF ochrana** pro formuláře  
- **Session management** s timeout
- **SQL injection ochrana** s PDO prepared statements

### 📱 **Technologie**
- **PHP 7.4+** backend
- **MySQL/MariaDB** databáze
- **Bootstrap 5** CSS framework
- **FontAwesome** ikony

## 🚀 Rychlá instalace

### 1. Stažení
```bash
# Stáhněte nejnovější release
wget https://github.com/supervisor-bit/cms-pohoda-antosovice/releases/latest/download/kemp_pohoda_cms_v2.0_20251122.zip

# Rozbalte na váš webserver
unzip kemp_pohoda_cms_v2.0_20251122.zip
```

### 2. Databáze
```sql
# Vytvořte MySQL databázi
CREATE DATABASE naturist_camp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Importujte strukturu
mysql -u root -p naturist_camp < database_complete_v2.sql
```

### 3. Konfigurace
```bash
# Zkopírujte a upravte config
cp config.php.template config.php
nano config.php  # Upravte databázové údaje
```

### 4. Oprávnění
```bash
chmod 755 uploads/
chown -R www-data:www-data uploads/
```

### 5. Přihlášení
- Otevřete: `https://yoursite.com/admin/`
- Login: `admin` / `password123`
- **Změňte heslo** v sekci Profil!

## 📖 Dokumentace

- 📋 **[DISTRIBUCE.md](DISTRIBUCE.md)** - Přehled funkcí a obsahu
- 🚀 **[INSTALACE_PRODUKCE.md](INSTALACE_PRODUKCE.md)** - Detailní instalační návod
- 📝 **[INSTALACE.md](INSTALACE.md)** - Základní instalace

## 🖼️ Screenshots

### Frontend
- Moderní responzivní design
- Glassmorphism 404 stránka  
- Bootstrap 5 komponenty

### Admin
- Intuitivní dashboard
- WYSIWYG editor pro stránky
- Quick Links správa

## 🛠️ Požadavky

### Minimální:
- **PHP 7.4+**
- **MySQL 5.7+** 
- **Apache/Nginx**
- **100MB** místa na disku

### Doporučené:
- **PHP 8.1+**
- **MySQL 8.0+**
- **SSL certifikát**
- **SSD storage**

## 🤝 Přispívání

Budeme rádi za příspěvky! Prosím:

1. Fork repository
2. Vytvořte feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit změny (`git commit -m 'Add some AmazingFeature'`)
4. Push branch (`git push origin feature/AmazingFeature`) 
5. Otevřete Pull Request

## 📄 Licence

Tento projekt je licencován pod MIT License - viz [LICENSE.txt](LICENSE.txt) pro detaily.

## 🆘 Podpora

- 🐛 **Issues:** [GitHub Issues](https://github.com/supervisor-bit/cms-pohoda-antosovice/issues)
- 💬 **Diskuze:** [GitHub Discussions](https://github.com/supervisor-bit/cms-pohoda-antosovice/discussions)

---

**Vytvořeno s ❤️ pro naturistické komunity**