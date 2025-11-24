// Sample data for Kindora Travel Website

// Places Data
window.placesData = [
  {
    id: 1,
    name: "Eiffel Tower",
    location: "Paris, France",
    image: "assets/images/places/eiffel_tower.avif",
    category: "city",
    budget: "medium",
    rating: 4.8,
    price: 299,
  },
  {
    id: 2,
    name: "Statue of Liberty",
    location: "New York, USA",
    image: "assets/images/places/statue_of_liberty.avif",
    category: "city",
    budget: "medium",
    rating: 4.7,
    price: 199,
  },
  {
    id: 3,
    name: "Sydney Opera House",
    location: "Sydney, Australia",
    image: "assets/images/places/sydney_opera_house.avif",
    category: "city",
    budget: "high",
    rating: 4.9,
    price: 599,
  },
  {
    id: 4,
    name: "Mount Fuji",
    location: "Japan",
    image: "assets/images/places/mount_fuji.avif",
    category: "mountain",
    budget: "medium",
    rating: 4.8,
    price: 199,
  },
  {
    id: 5,
    name: "Grand Canyon",
    location: "Arizona, USA",
    image: "assets/images/places/grand_canyon.avif",
    category: "nature",
    budget: "low",
    rating: 4.9,
    price: 99,
  },
  {
    id: 6,
    name: "Great Pyramid of Giza",
    location: "Egypt",
    image: "assets/images/places/great_pyramid.avif",
    category: "culture",
    budget: "low",
    rating: 4.7,
    price: 149,
  },
  {
    id: 7,
    name: "Santorini",
    location: "Greece",
    image: "assets/images/places/santorini.avif",
    category: "beach",
    budget: "medium",
    rating: 4.9,
    price: 399,
  },
  {
    id: 8,
    name: "Times Square",
    location: "New York, USA",
    image: "assets/images/places/times_square.avif",
    category: "city",
    budget: "high",
    rating: 4.6,
    price: 299,
  },
  {
    id: 9,
    name: "Machu Picchu",
    location: "Peru",
    image: "assets/images/places/machu_picchu.avif",
    category: "culture",
    budget: "medium",
    rating: 4.9,
    price: 349,
  },
  {
    id: 10,
    name: "Taj Mahal",
    location: "India",
    image: "assets/images/places/taj_mahal.avif",
    category: "culture",
    budget: "low",
    rating: 4.8,
    price: 199,
  },
];

// Packages Data
window.packagesData = [
  {
    id: 1,
    name: "Summer Escape",
    description:
      "Enjoy sunny beaches, tropical islands, and exotic adventures under the warm sun.",
    image: "assets/images/packages/summer.avif",
    price: 599,
    duration: 7,
    badge: "Best Seller",
    features: [
      { icon: "sun", text: "Beach Destinations" },
      { icon: "umbrella-beach", text: "Water Activities" },
      { icon: "cocktail", text: "Nightlife" },
    ],
  },
  {
    id: 2,
    name: "Winter Wonderland",
    description:
      "Experience snowy mountains, skiing adventures, and cozy winter retreats.",
    image: "assets/images/packages/winter.avif",
    price: 799,
    duration: 5,
    badge: "New",
    features: [
      { icon: "skiing", text: "Skiing" },
      { icon: "fire", text: "Cozy Lodges" },
      { icon: "snowflake", text: "Snow Activities" },
    ],
  },
  {
    id: 3,
    name: "Monsoon Magic",
    description:
      "Explore lush greenery, breathtaking waterfalls, and refreshing monsoon experiences.",
    image: "assets/images/packages/monsoon.avif",
    price: 399,
    duration: 6,
    badge: "Eco-Friendly",
    features: [
      { icon: "leaf", text: "Green Landscapes" },
      { icon: "water", text: "Waterfalls" },
      { icon: "seedling", text: "Eco Tourism" },
    ],
  },
];

// Deals Data
window.dealsData = [
  {
    id: 1,
    title: "Romantic Europe Escape",
    description: "Explore Paris, Venice & Santorini for couples",
    image: "assets/images/deals/europe_romantic.avif",
    originalPrice: 1999,
    salePrice: 1399,
    discount: "30% OFF",
  },
  {
    id: 2,
    title: "Asian Adventure Trail",
    description: "Thailand, Japan & Bali – culture, food & adventure",
    image: "assets/images/deals/asia_adventure.avif",
    originalPrice: 1599,
    salePrice: 1199,
    discount: "25% OFF",
  },
  {
    id: 3,
    title: "African Safari Adventure",
    description: "Safari in Kenya & South Africa – wildlife & nature",
    image: "assets/images/deals/africa_safari.avif",
    originalPrice: 2299,
    salePrice: 1839,
    discount: "20% OFF",
  },
  {
    id: 4,
    title: "USA Road Trip",
    description: "East to West coast – New York, Grand Canyon, LA",
    image: "assets/images/deals/usa_roadtrip.avif",
    originalPrice: 1799,
    salePrice: 1169,
    discount: "35% OFF",
  },
  {
    id: 5,
    title: "Luxury Maldives Retreat",
    description: "Private beaches, water villas & spa experience",
    image: "assets/images/deals/maldives_luxury.avif",
    originalPrice: 3999,
    salePrice: 2399,
    discount: "40% OFF",
  },
  {
    id: 6,
    title: "Mystical Egypt Journey",
    description: "Pyramids, Nile Cruise & Ancient temples",
    image: "assets/images/deals/egypt_mystical.avif",
    originalPrice: 1499,
    salePrice: 1049,
    discount: "30% OFF",
  },
];

// Wonders Data
window.wondersData = [
  {
    id: 1,
    name: "Taj Mahal",
    location: "India",
    image: "assets/images/wonders/taj_mahal.avif",
    rating: 4.9,
  },
  {
    id: 2,
    name: "Great Wall of China",
    location: "China",
    image: "assets/images/wonders/great_wall.avif",
    rating: 4.8,
  },
  {
    id: 3,
    name: "Christ the Redeemer",
    location: "Brazil",
    image: "assets/images/wonders/christ_redeemer.avif",
    rating: 4.7,
  },
  {
    id: 4,
    name: "Machu Picchu",
    location: "Peru",
    image: "assets/images/wonders/machu_picchu.avif",
    rating: 4.9,
  },
  {
    id: 5,
    name: "Chichen Itza",
    location: "Mexico",
    image: "assets/images/wonders/chichen_itza.avif",
    rating: 4.6,
  },
  {
    id: 6,
    name: "Petra",
    location: "Jordan",
    image: "assets/images/wonders/petra.avif",
    rating: 4.8,
  },
  {
    id: 7,
    name: "Roman Colosseum",
    location: "Italy",
    image: "assets/images/wonders/colosseum.avif",
    rating: 4.7,
  },
];

// Testimonials Data
window.testimonialsData = [
  {
    id: 1,
    name: "Sarah Johnson",
    title: "Travel Blogger",
    image:
      "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face",
    rating: 5,
    comment:
      "Kindora made our dream trip to Santorini absolutely perfect! The attention to detail and local recommendations were spot on.",
  },
  {
    id: 2,
    name: "Michael Chen",
    title: "Wildlife Photographer",
    image:
      "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face",
    rating: 5,
    comment:
      "The African safari package exceeded all expectations. We saw the Big Five and had the most incredible wildlife encounters!",
  },
  {
    id: 3,
    name: "Emma Rodriguez",
    title: "Adventure Seeker",
    image:
      "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face",
    rating: 5,
    comment:
      "From booking to the actual trip, everything was seamless. The local guides were knowledgeable and friendly.",
  },
];

// Volunteers Data
window.volunteersData = [
  {
    title: "First Year Volunteers",
    description:
      "Our journey begins with passionate travelers and volunteers joining hands to explore and promote sustainable tourism.",
  },
  {
    title: "Local Partners",
    description:
      "Kindora has started collaborations with local guides and cultural storytellers to bring authentic experiences.",
  },
  {
    title: "Sustainability Ambassadors",
    description:
      "Early volunteers working on eco-travel, heritage protection, and community-led projects.",
  },
  {
    title: "Launch Event",
    description:
      "Kindora officially launched in 2025 with a vision to inspire world exploration.",
  },
  {
    title: "Virtual Culture Exchange",
    description:
      "Hosted our first online cultural session connecting travelers from different continents.",
  },
  {
    title: "Eco & Community Activities",
    description:
      "Beginning small eco-drives and heritage awareness campaigns with local groups.",
  },
  {
    title: "Countries Featured",
    description:
      "Within the first year, Kindora highlights major attractions from Asia, Europe, and Africa.",
  },
  {
    title: "Community Growth",
    description:
      "Thousands of explorers inspired to travel responsibly since our launch.",
  },
  {
    title: "Future Vision",
    description:
      "Expanding our global reach by adding more destinations, volunteer programs, and cultural events in upcoming years.",
  },
];

// Reviews Data
window.reviewsData = [
  {
    name: "Aditi Sharma",
    rating: 5,
    comment:
      "An unforgettable experience! The Taj Mahal is truly magical at sunrise.",
  },
  {
    name: "Rahul Verma",
    rating: 4,
    comment: "Well organized trip, smooth booking experience with Kindora!",
  },
  {
    name: "Priya Patel",
    rating: 5,
    comment: "Amazing service and great value for money. Highly recommended!",
  },
  {
    name: "David Wilson",
    rating: 5,
    comment:
      "The local guides were fantastic and the itinerary was perfectly planned.",
  },
  {
    name: "Maria Garcia",
    rating: 4,
    comment:
      "Beautiful destinations and excellent customer support throughout the journey.",
  },
];

// FAQ Data
window.faqData = [
  {
    question: "What is Kindora?",
    answer:
      "Kindora is a comprehensive travel platform that helps you explore the world with a focus on authentic experiences, cultural immersion, and sustainable tourism. We provide detailed guides, booking services, and personalized recommendations.",
  },
  {
    question: "Is Kindora free to use?",
    answer:
      "Yes! Our travel guides, destination information, and basic features are completely free. We only charge for actual bookings and premium services like personalized itineraries and concierge support.",
  },
  {
    question: "Can I suggest places to add?",
    answer:
      "Absolutely! We love hearing from our community. You can suggest new destinations, share your travel experiences, and even contribute to our travel guides. Visit our contact page to submit your suggestions.",
  },
  {
    question: "What services does Kindora provide?",
    answer:
      "We offer comprehensive travel planning including destination guides, accommodation booking, activity reservations, transportation options, travel insurance, and 24/7 customer support. We also provide cultural insights, local recommendations, and sustainable travel options.",
  },
  {
    question: "How do I book a trip?",
    answer:
      "Booking is simple! Browse our destinations, select your preferred package or create a custom itinerary, choose your dates, and complete the booking process. Our team will handle all the details and provide you with a comprehensive travel plan.",
  },
  {
    question: "What if I need to cancel my trip?",
    answer:
      "We understand that plans can change. Our cancellation policy varies by package and timing, but we always try to accommodate our travelers. Contact our support team as soon as possible to discuss your options.",
  },
];
