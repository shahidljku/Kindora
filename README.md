

# Kindora Travel Website

A comprehensive travel website built with PHP, MySQL, HTML, CSS, and JavaScript. Kindora provides a platform for travelers to explore destinations, book packages, and share their experiences.

## Features

### 🎯 Core Features
- **User Authentication** - Registration, login, password reset
- **Destination Explorer** - Browse and search destinations with filters
- **Travel Packages** - Book curated travel packages
- **Activities** - Discover things to do at each destination
- **Reviews & Ratings** - User reviews and rating system
- **Newsletter** - Email subscription for travel deals
- **Admin Panel** - Content management system
- **Responsive Design** - Mobile-first approach

### 🌟 Advanced Features
- **Dynamic Content Loading** - AJAX-powered content updates
- **Image Lazy Loading** - Performance optimization
- **Search & Filtering** - Advanced search with multiple filters
- **Carousel Galleries** - Interactive image carousels
- **FAQ System** - Expandable FAQ sections
- **Counter Animations** - Animated statistics
- **Smooth Scrolling** - Enhanced user experience
- **Touch/Swipe Support** - Mobile gesture support

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with CSS Grid & Flexbox
- **Icons**: Font Awesome 6.0
- **Fonts**: Google Fonts (Poppins)

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/kindora-travel.git
   cd kindora-travel
   ```

2. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p
   CREATE DATABASE kindora_travel;
   
   # Import schema
   mysql -u root -p kindora_travel < database/schema.sql
   ```

3. **Configure Database Connection**
   Edit `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'kindora_travel';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

4. **Set Up Web Server**
   
   **Apache (.htaccess)**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

   **Nginx**
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

5. **File Permissions**
   ```bash
   chmod 755 assets/
   chmod 644 assets/css/*
   chmod 644 assets/js/*
   chmod 644 assets/images/*
   ```

6. **Access the Website**
   Open your browser and navigate to:
   ```
   http://localhost/kindora-travel
   ```

## Project Structure

```
kindora-travel/
├── assets/
│   ├── css/
│   │   └── styles.css          # Main stylesheet
│   ├── js/
│   │   ├── main.js            # Main JavaScript
│   │   └── data.js            # Sample data
│   ├── images/
│   │   ├── places/            # Destination images
│   │   ├── packages/          # Package images
│   │   ├── banner/            # Banner images
│   │   └── 7wonders/          # Wonders images
│   └── videos/
│       └── bgvideo.mp4        # Background video
├── config/
│   └── database.php           # Database configuration
├── includes/
│   ├── auth.php              # Authentication system
│   └── logout.php            # Logout functionality
├── pages/
│   ├── login.php             # Login page
│   ├── booking.php           # Booking page
│   ├── explore.php           # Explore destinations
│   ├── things_to_do.php      # Activities page
│   └── profile.php           # User profile
├── database/
│   └── schema.sql            # Database schema
├── index.php                 # Homepage
└── README.md                 # This file
```

## Database Schema

### Main Tables
- **users** - User accounts and profiles
- **destinations** - Travel destinations
- **packages** - Travel packages
- **bookings** - User bookings
- **reviews** - User reviews and ratings
- **activities** - Things to do
- **faqs** - Frequently asked questions
- **newsletter_subscriptions** - Email subscriptions

### Key Features
- **Foreign Key Constraints** - Data integrity
- **Indexes** - Performance optimization
- **Soft Deletes** - Data preservation
- **Timestamps** - Audit trail

## API Endpoints

### Authentication
- `POST /pages/login.php` - User login
- `POST /pages/register.php` - User registration
- `GET /includes/logout.php` - User logout

### Content
- `GET /pages/explore.php` - Get destinations
- `GET /pages/things_to_do.php` - Get activities
- `GET /pages/booking.php` - Get packages

### User Actions
- `POST /pages/booking.php` - Create booking
- `POST /pages/profile.php` - Update profile
- `POST /pages/review.php` - Submit review

## Configuration

### Environment Variables
Create a `.env` file:
```env
DB_HOST=localhost
DB_NAME=kindora_travel
DB_USER=your_username
DB_PASS=your_password
SITE_URL=http://localhost/kindora-travel
```

### Email Configuration
Update email settings in `includes/auth.php`:
```php
// SMTP Configuration
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'your-email@gmail.com';
$smtp_password = 'your-app-password';
```

## Customization

### Adding New Destinations
1. Add destination data to database
2. Upload images to `assets/images/places/`
3. Update `assets/js/data.js` if using static data

### Styling
- Modify `assets/css/styles.css`
- CSS variables in `:root` for easy theming
- Responsive breakpoints: 768px, 480px

### JavaScript
- Main functionality in `assets/js/main.js`
- Sample data in `assets/js/data.js`
- Modular structure for easy extension

## Security Features

- **Password Hashing** - bcrypt encryption
- **SQL Injection Prevention** - Prepared statements
- **XSS Protection** - Input sanitization
- **CSRF Protection** - Token validation
- **Session Management** - Secure sessions
- **Input Validation** - Server-side validation

## Performance Optimization

- **Image Lazy Loading** - Faster page loads
- **CSS/JS Minification** - Reduced file sizes
- **Database Indexing** - Faster queries
- **Caching** - Reduced server load
- **CDN Ready** - Asset optimization

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

## Support

For support and questions:
- Email: support@kindora.com
- Documentation: [docs.kindora.com](https://docs.kindora.com)
- Issues: [GitHub Issues](https://github.com/yourusername/kindora-travel/issues)

## Changelog

### Version 1.0.0 (2025-01-01)
- Initial release
- User authentication system
- Destination explorer
- Booking system
- Review system
- Admin panel
- Responsive design

## Roadmap

### Version 1.1.0
- [ ] Payment integration
- [ ] Email notifications
- [ ] Advanced search filters
- [ ] Social media integration

### Version 1.2.0
- [ ] Mobile app
- [ ] Real-time chat
- [ ] Multi-language support
- [ ] Advanced analytics

---

**Made with ❤️ by the Kindora Team**
