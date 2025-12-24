<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsContent;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $newsData = [
            [
                'title' => 'New Premium Collection Launch 2024',
                'is_top_news' => true,
                'content' => '<h2>Introducing Our Finest Collection</h2><p>We are thrilled to announce the launch of our Premium Collection 2024, featuring handcrafted furniture pieces that blend traditional craftsmanship with contemporary design.</p><img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800" alt="Premium furniture"><p>Each piece in this collection represents months of meticulous work by our master artisans, using only the finest teak wood sourced from sustainable forests.</p>',
            ],
            [
                'title' => 'Company Celebrates 15 Years of Excellence',
                'is_top_news' => false,
                'content' => '<h2>A Milestone Anniversary</h2><p>This year marks our 15th anniversary in the furniture industry. From a small workshop to an international brand, our journey has been remarkable.</p><img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?w=800" alt="Celebration"><p>We extend our heartfelt thanks to our customers, partners, and the dedicated team who made this possible.</p>',
            ],
            [
                'title' => 'Expansion to European Market',
                'is_top_news' => false,
                'content' => '<h2>New Markets, New Opportunities</h2><p>We are excited to announce our expansion into the European market. Our showrooms will soon be available in Germany, France, and the Netherlands.</p><img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800" alt="Europe expansion"><p>This expansion brings our authentic Indonesian craftsmanship closer to European customers who appreciate quality and sustainability.</p>',
            ],
            [
                'title' => 'Award-Winning Design Recognition',
                'is_top_news' => false,
                'content' => '<h2>International Design Award</h2><p>Our Modern Heritage Chair has won the prestigious International Furniture Design Award 2024. This recognition highlights our commitment to innovative design.</p><img src="https://images.unsplash.com/photo-1506439773649-6e0eb8cfb237?w=800" alt="Award"><p>We are honored to receive this award and will continue pushing the boundaries of furniture design.</p>',
            ],
            [
                'title' => 'Sustainable Manufacturing Certification',
                'is_top_news' => false,
                'content' => '<h2>FSC Certification Renewed</h2><p>We are proud to announce the renewal of our FSC (Forest Stewardship Council) certification, reaffirming our commitment to sustainable forestry practices.</p><img src="https://images.unsplash.com/photo-1448375240586-882707db888b?w=800" alt="Forest"><p>Every piece of furniture you purchase supports responsible forest management.</p>',
            ],
            [
                'title' => 'New Factory Technology Upgrade',
                'is_top_news' => false,
                'content' => '<h2>Investing in Precision</h2><p>Our factory has undergone a significant technology upgrade with state-of-the-art CNC machines that enhance precision while maintaining our handcrafted finish.</p><img src="https://images.unsplash.com/photo-1565793298595-6a879b1d9492?w=800" alt="Factory"><p>Technology and tradition work hand in hand to deliver the best quality products.</p>',
            ],
            [
                'title' => 'Partnership with Luxury Hotels',
                'is_top_news' => false,
                'content' => '<h2>Furnishing Five-Star Properties</h2><p>We have secured partnerships with several five-star hotel chains to supply custom furniture for their properties across Asia and the Middle East.</p><img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800" alt="Hotel"><p>Our furniture will grace the lobbies and suites of world-renowned hospitality brands.</p>',
            ],
            [
                'title' => 'Designer Collaboration Announcement',
                'is_top_news' => false,
                'content' => '<h2>Exclusive Designer Collection</h2><p>We are partnering with renowned Scandinavian designer Erik Johansson for a limited-edition collection that merges Nordic minimalism with Indonesian craftsmanship.</p><img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800" alt="Design"><p>This exclusive collaboration will be available in Q3 2024.</p>',
            ],
            [
                'title' => 'Customer Appreciation Event',
                'is_top_news' => false,
                'content' => '<h2>Thank You Celebration</h2><p>Join us for our annual Customer Appreciation Day on December 20th at our main showroom. Enjoy special discounts, live demonstrations, and refreshments.</p><img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800" alt="Event"><p>This is our way of saying thank you to our loyal customers.</p>',
            ],
            [
                'title' => 'New Outdoor Collection Preview',
                'is_top_news' => false,
                'content' => '<h2>Outdoor Living Redefined</h2><p>Get a sneak peek at our upcoming Outdoor Collection, featuring weather-resistant teak furniture designed for ultimate comfort and durability.</p><img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800" alt="Outdoor"><p>Perfect for patios, gardens, and pool areas, this collection will be available next spring.</p>',
            ],
            [
                'title' => 'E-commerce Platform Launch',
                'is_top_news' => false,
                'content' => '<h2>Shop Online, Delivered Worldwide</h2><p>We have launched our new e-commerce platform, making it easier than ever to browse and purchase our furniture from anywhere in the world.</p><img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800" alt="Ecommerce"><p>Enjoy seamless shopping with worldwide delivery options.</p>',
            ],
            [
                'title' => 'Craftsman Training Program Graduation',
                'is_top_news' => false,
                'content' => '<h2>50 New Master Craftsmen</h2><p>We celebrated the graduation of 50 trainees from our Master Craftsman Program. These skilled artisans have completed two years of intensive training.</p><img src="https://images.unsplash.com/photo-1544717305-2782549b5136?w=800" alt="Graduation"><p>The future of furniture craftsmanship is in excellent hands.</p>',
            ],
            [
                'title' => 'Vintage Collection Restoration Service',
                'is_top_news' => false,
                'content' => '<h2>Breathe New Life Into Old Favorites</h2><p>Introducing our new furniture restoration service. Bring your beloved vintage pieces to us for professional restoration by our expert craftsmen.</p><img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800" alt="Restoration"><p>Preserve family heirlooms and antique furniture with our specialized service.</p>',
            ],
            [
                'title' => 'Trade Fair Success in Milan',
                'is_top_news' => false,
                'content' => '<h2>Milan Furniture Fair Highlights</h2><p>Our booth at the Milan Furniture Fair attracted over 10,000 visitors. We received overwhelming interest in our handcrafted Indonesian designs.</p><img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800" alt="Trade fair"><p>Thank you to everyone who visited us and showed appreciation for our work.</p>',
            ],
            [
                'title' => 'Holiday Gift Guide 2024',
                'is_top_news' => false,
                'content' => '<h2>Perfect Gifts for Furniture Lovers</h2><p>Looking for the perfect holiday gift? Check out our curated gift guide featuring small accent pieces, home accessories, and gift cards.</p><img src="https://images.unsplash.com/photo-1607469256872-48074e807b0f?w=800" alt="Gifts"><p>Make this holiday season special with timeless craftsmanship.</p>',
            ],
            [
                'title' => 'Behind the Scenes: The Making of a Chair',
                'is_top_news' => false,
                'content' => '<h2>From Tree to Living Room</h2><p>Ever wondered how our chairs are made? Follow along as we document the 40-step process of creating a single dining chair, from raw wood to finished product.</p><img src="https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=800" alt="Behind the scenes"><p>Each chair represents hours of dedicated craftsmanship and attention to detail.</p>',
            ],
            [
                'title' => 'Quality Guarantee Extension',
                'is_top_news' => false,
                'content' => '<h2>Extended Warranty Program</h2><p>We are extending our quality guarantee from 5 years to 10 years on all premium collection items. Our confidence in quality is your peace of mind.</p><img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800" alt="Warranty"><p>Invest in furniture that lasts a lifetime.</p>',
            ],
            [
                'title' => 'Interior Designer Partnership Program',
                'is_top_news' => false,
                'content' => '<h2>Professional Partner Benefits</h2><p>Interior designers and architects can now join our exclusive partnership program, offering special pricing, priority production, and dedicated support.</p><img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800" alt="Design"><p>Partner with us to deliver exceptional furniture solutions to your clients.</p>',
            ],
            [
                'title' => 'Showroom Renovation Complete',
                'is_top_news' => false,
                'content' => '<h2>Experience Our New Space</h2><p>Our main showroom has been completely renovated with a modern design that better showcases our furniture collections. Visit us to experience the transformation.</p><img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800" alt="Showroom"><p>Our new space reflects our commitment to excellence and customer experience.</p>',
            ],
            [
                'title' => 'Annual Sustainability Report Released',
                'is_top_news' => false,
                'content' => '<h2>Transparency in Action</h2><p>We have published our annual sustainability report, detailing our environmental impact, social initiatives, and goals for the coming year.</p><img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800" alt="Sustainability"><p>Read the full report on our website to learn more about our commitment to a sustainable future.</p>',
            ],
        ];

        foreach ($newsData as $data) {
            $news = News::create([
                'title' => $data['title'],
                'is_top_news' => $data['is_top_news'],
                'create_by' => $user?->id,
            ]);

            NewsContent::create([
                'news_id' => $news->id,
                'content' => $data['content'],
            ]);
        }
    }
}
