# Sportex Arilje - Custom Sports Equipment Website

A professional bilingual (Serbian/English) e-commerce website for custom sports uniforms and equipment manufacturing.

## 🏆 About Sportex

Sportex Arilje specializes in creating high-quality custom sports uniforms and equipment for teams and organizations. Based in Arilje, Serbia, we provide personalized sportswear solutions with custom designs, team colors, and player personalization.

## 🎯 Features

- **Bilingual Support**: Full Serbian and English language support
- **Custom Sports Equipment**: Specialized uniforms for multiple sports
- **Responsive Design**: Mobile-first Bootstrap-based design
- **Interactive Galleries**: FancyBox-powered product showcases
- **Contact System**: PHP-based contact form with email integration
- **Animation Effects**: Smooth AOS (Animate On Scroll) transitions

## 🏅 Sports Categories

- **Soccer/Football** - Custom jerseys, goalkeeper kits, training suits
- **Basketball** - Team uniforms and training wear
- **Volleyball** - Specialized volleyball team kits
- **Rugby** - Custom rugby jerseys and equipment
- **American Football** - Professional-grade football uniforms
- **Martial Arts** - Combat sports apparel
- **Miscellaneous** - Sports accessories, bags, caps, protective gear

## 🛠️ Technology Stack

### Frontend
- **HTML5/CSS3** - Semantic markup and modern styling
- **Bootstrap 5** - Responsive grid system and components
- **JavaScript/jQuery** - Interactive functionality
- **AOS Library** - Scroll animations
- **FancyBox** - Image gallery lightbox
- **Font Awesome** - Icon library

### Backend
- **PHP** - Contact form processing
- **Email Integration** - Automated email system

### Design
- **Custom CSS** - Brand-specific styling with #b22222 primary color
- **Google Fonts** - Open Sans and Dosis typography
- **Responsive Images** - Optimized product galleries

## 📁 Project Structure

```
sportex/
├── index.html              # Serbian homepage
├── index-en.html           # English homepage
├── oprema.html            # Equipment overview (Serbian)
├── equipment.html         # Equipment overview (English)
├── kontakt.html           # Contact page (Serbian)
├── contact.html           # Contact page (English)
├── sports/                # Sport-specific pages
│   ├── soccer.html        # Soccer equipment (Serbian)
│   ├── soccer-en.html     # Soccer equipment (English)
│   ├── basketball.html    # Basketball equipment
│   ├── volleyball.html    # Volleyball equipment
│   ├── rugby.html         # Rugby equipment
│   ├── football.html      # American football equipment
│   ├── martial-arts.html  # Martial arts equipment
│   └── misc.html          # Miscellaneous equipment
├── assets/
│   ├── css/
│   │   ├── style.css      # Main stylesheet
│   │   └── style-sports.css # Sports-specific styles
│   ├── js/
│   │   ├── main.js        # Core functionality
│   │   └── index.js       # Homepage interactions
│   ├── img/
│   │   ├── png/           # Product images by category
│   │   ├── gallery/       # Gallery images
│   │   ├── flags/         # Language selector flags
│   │   └── logos/         # Brand logos
│   ├── php/
│   │   └── email.php      # Contact form handler
│   └── vendor/            # Third-party libraries
└── forms/
    └── contact.php        # Alternative contact form
```

## 🚀 Getting Started

### Prerequisites
- Web server with PHP support (for contact forms)
- Modern web browser

### Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/ds185531/sportex.git
   ```

2. Set up a local web server or upload to your hosting provider

3. Configure email settings in `assets/php/email.php`:
   ```php
   $to = "your-email@domain.com"; // Replace with your email
   ```

4. Access the website through your web server

### Local Development
For local development, you can use:
- **XAMPP/WAMP** - For PHP support
- **Live Server** - For static file serving (contact forms won't work)
- **PHP Built-in Server**:
  ```bash
  php -S localhost:8000
  ```

## 📧 Contact Configuration

The contact system uses PHP mail functionality. Key files:
- `assets/php/email.php` - Main email handler
- `kontakt.html` / `contact.html` - Contact forms
- Email destination: `sportexdragan@gmail.com`

## 🌐 Language Support

The website supports Serbian (primary) and English:
- **Serbian Pages**: `index.html`, `oprema.html`, `kontakt.html`
- **English Pages**: `index-en.html`, `equipment.html`, `contact.html`
- **Sports Pages**: Each has both `-en` and Serbian versions

## 📱 Contact Information

- **Email**: sportexdragan@gmail.com
- **Phone**: +381 (0) 63 691-711
- **Phone**: +381 (0) 62 710-911
- **Instagram**: [@stx_sportskaoprema](https://www.instagram.com/stx_sportskaoprema/)
- **Facebook**: [sportex.rs](https://www.facebook.com/sportex.rs)
- **Location**: Sportex, Cerova, Arilje, 31230 Arilje, Serbia

## 🎨 Customization

### Colors
Primary brand color: `#b22222` (defined in `assets/css/style.css`)

### Adding New Products
1. Add product images to appropriate `/assets/img/png/[category]/` folder
2. Update the respective sport page gallery section
3. Add FancyBox data attributes for lightbox functionality

### Adding New Sports Categories
1. Create new HTML files in `/sports/` directory
2. Follow existing naming convention (`sport.html` and `sport-en.html`)
3. Update navigation menus in all pages
4. Create corresponding image folders

## 📄 License

This project is built on the Butterfly template by BootstrapMade. Please review their licensing terms for commercial use.

## 🤝 Contributing

For business inquiries and custom orders, please contact us through:
- Website contact forms
- Direct email: sportexdragan@gmail.com
- Social media channels

---

**Sportex Arilje** - Premium Quality Sports Equipment | Najkvalitetnija sportska oprema

