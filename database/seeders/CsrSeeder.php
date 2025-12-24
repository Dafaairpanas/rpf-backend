<?php

namespace Database\Seeders;

use App\Models\Csr;
use App\Models\CsrContent;
use App\Models\User;
use Illuminate\Database\Seeder;

class CsrSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $csrData = [
            [
                'title' => 'Tree Planting Initiative 2024',
                'content' => '<h2>Reforestation Program</h2><p>Our commitment to sustainability includes planting over 10,000 trees annually in deforested areas. This initiative helps restore natural habitats and supports local communities.</p><img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800" alt="Tree planting"><p>Through partnerships with local farmers, we ensure that every tree planted contributes to long-term environmental restoration.</p>',
            ],
            [
                'title' => 'Local Artisan Empowerment',
                'content' => '<h2>Supporting Local Craftsmen</h2><p>We work directly with local artisans, providing them with fair wages and skill development opportunities. Our program has benefited over 500 craftsmen families.</p><img src="https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=800" alt="Artisan work"><p>Each piece of furniture carries the legacy of traditional craftsmanship passed down through generations.</p>',
            ],
            [
                'title' => 'Clean Water Project',
                'content' => '<h2>Access to Clean Water</h2><p>We have installed water purification systems in 20 villages near our manufacturing facilities. Over 5,000 people now have access to clean drinking water.</p><img src="https://images.unsplash.com/photo-1538300342682-cf57afb97285?w=800" alt="Clean water"><p>This project reflects our commitment to community well-being beyond just business operations.</p>',
            ],
            [
                'title' => 'Education Scholarship Fund',
                'content' => '<h2>Investing in Future Generations</h2><p>Our scholarship program supports children of local craftsmen to pursue higher education. Since 2020, we have funded 150 scholarships.</p><img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800" alt="Education"><p>Education is the foundation for sustainable community development.</p>',
            ],
            [
                'title' => 'Sustainable Forestry Partnership',
                'content' => '<h2>Responsible Wood Sourcing</h2><p>All our teak wood comes from certified sustainable forests. We partner with FSC-certified suppliers to ensure responsible forestry practices.</p><img src="https://images.unsplash.com/photo-1448375240586-882707db888b?w=800" alt="Forest"><p>Sustainability is not just a choice, it is our responsibility for future generations.</p>',
            ],
            [
                'title' => 'Women Empowerment Workshop',
                'content' => '<h2>Empowering Women Artisans</h2><p>Our dedicated women empowerment program provides training in furniture finishing and design. Over 100 women have joined our workforce through this initiative.</p><img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=800" alt="Women empowerment"><p>Gender equality in the workplace creates stronger communities.</p>',
            ],
            [
                'title' => 'Zero Waste Manufacturing',
                'content' => '<h2>Reducing Environmental Impact</h2><p>We have implemented a zero-waste policy in our manufacturing facilities. Wood shavings are recycled into biofuel, and all packaging materials are biodegradable.</p><img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=800" alt="Zero waste"><p>Every step of our production process is designed with the environment in mind.</p>',
            ],
            [
                'title' => 'Community Health Center',
                'content' => '<h2>Healthcare for All</h2><p>We sponsor a community health center that provides free medical checkups and basic healthcare services to factory workers and their families.</p><img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800" alt="Healthcare"><p>Healthy workers are happy workers, and we invest in their well-being.</p>',
            ],
            [
                'title' => 'Youth Skills Training',
                'content' => '<h2>Preparing the Next Generation</h2><p>Our vocational training program teaches traditional woodworking skills to young people in the community. Over 200 youth have graduated from our program.</p><img src="https://images.unsplash.com/photo-1544717305-2782549b5136?w=800" alt="Training"><p>Preserving traditional craftsmanship while adapting to modern demands.</p>',
            ],
            [
                'title' => 'Renewable Energy Initiative',
                'content' => '<h2>Solar Powered Factory</h2><p>Our main manufacturing facility is now powered by 70% renewable energy through solar panels. We aim to achieve 100% by 2025.</p><img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800" alt="Solar energy"><p>Clean energy for cleaner production and a healthier planet.</p>',
            ],
            [
                'title' => 'Marine Conservation Project',
                'content' => '<h2>Protecting Our Oceans</h2><p>In partnership with local environmental groups, we support coastal cleanup and coral reef restoration projects in nearby marine areas.</p><img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800" alt="Ocean"><p>Our responsibility extends beyond land to the seas that surround our communities.</p>',
            ],
            [
                'title' => 'Traditional Craft Documentation',
                'content' => '<h2>Preserving Cultural Heritage</h2><p>We document traditional woodworking techniques through video archives and written records, ensuring this knowledge is preserved for future generations.</p><img src="https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=800" alt="Traditional craft"><p>Culture and craftsmanship are intertwined in every piece we create.</p>',
            ],
            [
                'title' => 'Emergency Relief Fund',
                'content' => '<h2>Community Support in Crisis</h2><p>Our emergency relief fund provides immediate assistance to community members affected by natural disasters. We have disbursed aid to over 300 families during floods and earthquakes.</p><img src="https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800" alt="Relief"><p>Standing by our community in times of need.</p>',
            ],
            [
                'title' => 'Eco-Friendly Packaging',
                'content' => '<h2>Sustainable Shipping Solutions</h2><p>All our products are shipped using 100% recyclable and biodegradable packaging materials. We have eliminated plastic from our supply chain.</p><img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800" alt="Eco packaging"><p>Protecting the environment from factory to doorstep.</p>',
            ],
            [
                'title' => 'Local Economy Boost',
                'content' => '<h2>Supporting Local Businesses</h2><p>We prioritize local suppliers for all non-wood materials, injecting over $1 million annually into the local economy through our supply chain.</p><img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800" alt="Local economy"><p>When we grow, our community grows with us.</p>',
            ],
        ];

        foreach ($csrData as $data) {
            $csr = Csr::create([
                'title' => $data['title'],
                'create_by' => $user?->id,
            ]);

            CsrContent::create([
                'csr_id' => $csr->id,
                'content' => $data['content'],
            ]);
        }
    }
}
